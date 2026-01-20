<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDevisRequest;
use App\Http\Resources\DevisResource;
use App\Models\Devis;
use App\Models\LigneDevis;
use App\Services\PricingService;
use App\Models\Commande;
use App\Models\ArticleCommande;
use App\Models\Facture;
use App\Models\ArticleFacture;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class DevisController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Devis::with(['client', 'lignes']);

        if ($request->has('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'like', "%{$search}%")
                                  ->orWhere('tel', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $devis = $query->paginate($perPage);

        return DevisResource::collection($devis);
    }

    public function store(StoreDevisRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // 1. Créer le devis
            $devisData = $request->except('lignes');
            $devisData['statut'] = 'accepte'; // Seule valeur logique avec ton enum réduit
            $devis = Devis::create($devisData);

            // 2. Créer les lignes du devis
            foreach ($request->lignes as $index => $ligneData) {
                $prixCalcule = PricingService::calculerPrixUnitaire(
                    $ligneData['produit'],
                    $ligneData['largeur'] ?? null,
                    $ligneData['hauteur'] ?? null
                );

                $devis->lignes()->create([
                    'produit' => $ligneData['produit'],
                    'categorie' => $ligneData['categorie'] ?? null,
                    'description' => $ligneData['description'] ?? null,
                    'largeur' => $ligneData['largeur'] ?? null,
                    'hauteur' => $ligneData['hauteur'] ?? null,
                    'quantite' => $ligneData['quantite'],
                    'aluminium' => $ligneData['aluminium'] ?? null,
                    'vitrage' => $ligneData['vitrage'] ?? null,
                    'prix_unitaire' => $prixCalcule,
                    'sous_total' => $prixCalcule * $ligneData['quantite'],
                    'ordre' => $index,
                ]);
            }

            $devis->calculerTotaux();
            $devis->refresh();

            // 3. Créer la commande
            $commande = Commande::create([
                'client_id' => $devis->client_id,
                'devis_id' => $devis->id,
                'date_commande' => now(),
                'date_livraison' => $devis->date_validite,
                'statut' => 'En attente',
                'montant_ht' => $devis->total_ht,
                'montant_ttc' => $devis->total_ttc,
                'notes' => "Créée automatiquement depuis devis #{$devis->id}",
            ]);

            // 4. Articles commande
            foreach ($devis->lignes as $ligne) {
                $dimensions = '';
                if ($ligne->largeur && $ligne->hauteur) {
                    $dimensions = "{$ligne->largeur}m × {$ligne->hauteur}m";
                   
                }

                ArticleCommande::create([
                    'commande_id' => $commande->id,
                    'produit' => $ligne->produit,
                    'quantite' => $ligne->quantite,
                    'dimensions' => $dimensions ?: $ligne->description,
                    'prix' => $ligne->prix_unitaire,
                ]);
            }

            // 5. Créer la facture
            $facture = Facture::create([
                'commande_id' => $commande->id,
                'client_id' => $devis->client_id,
                'date_emission' => now(),
                'date_echeance' => now()->addDays(30),
                'montant_ht' => $devis->total_ht,
                'tva' => 0,
                'montant_ttc' => $devis->total_ttc,
                'montant_paye' => 0,
                'statut' => 'Non payée',
                'mode_paiement' => null,
                'notes' => "Facture auto pour devis #{$devis->id}",
            ]);

            // 6. Articles facture
            foreach ($commande->articles as $article) {
                ArticleFacture::create([
                    'facture_id' => $facture->id,
                    'designation' => $article->produit,
                    'quantite' => $article->quantite,
                    'prix_unitaire' => $article->prix,
                    'total' => $article->prix * $article->quantite,
                ]);
            }

            // 7. 🔥 Mettre à jour les stats du client
            $devis->client->refreshStats(); // ← AJOUT ICI

            DB::commit();

            return response()->json([
                'message' => 'Devis, commande et facture créés automatiquement.',
                'data' => new DevisResource($devis->load(['client', 'lignes'])),
                'commande_id' => $commande->id,
                'facture_id' => $facture->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création des documents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Devis $devis): JsonResponse
    {
        $devis->load(['client', 'lignes']);
        return response()->json(['data' => new DevisResource($devis)]);
    }

    public function update($id): JsonResponse
    {
        return response()->json([
            'message' => 'Modification non autorisée : les devis sont définitifs.'
        ], 403);
    }

    public function destroy($id): JsonResponse
    {
        return response()->json([
            'message' => 'Suppression non autorisée : les devis sont conservés.'
        ], 403);
    }
}