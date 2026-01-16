<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFactureRequest;
use App\Http\Requests\UpdateFactureRequest;
use App\Http\Resources\FactureResource;
use App\Models\Facture;
use App\Models\Commande;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Facture::with(['client', 'articles', 'commande']);

        if ($request->has('statut') && $request->statut !== 'Tous') {
            $query->where('statut', '=', $request->statut);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_facture', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'like', "%{$search}%")
                                  ->orWhere('telephone', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $factures = $query->paginate($perPage);

        return FactureResource::collection($factures);
    }

    public function store(StoreFactureRequest $request, ClientService $clientService): JsonResponse
    {
        try {
            DB::beginTransaction();

            // 1. Récupérer la commande pour obtenir le client
            $commande = Commande::findOrFail($request->commande_id);
            $client = $commande->client;

            // 2. Préparer les données de la facture
            $factureData = $request->validated();
            $factureData['client_id'] = $client->id;

            // 3. 🔥 Générer le numéro de facture automatiquement
            $factureData['numero_facture'] = Facture::genererNumero();

            // 4. Créer la facture
            $facture = Facture::create($factureData);

            // 5. Gérer les articles si fournis
            if ($request->has('articles')) {
                foreach ($request->articles as $articleData) {
                    $articleData['total'] = ($articleData['prix_unitaire'] ?? 0) * ($articleData['quantite'] ?? 1);
                    $facture->articles()->create($articleData);
                }

                // Recalculer les montants à partir des articles
                $montantHT = $facture->articles->sum('total');
                $facture->update([
                    'montant_ht' => $montantHT,
                    'tva' => 0,
                    'montant_ttc' => $montantHT,
                ]);
            }

            // 6. 🔥 Mettre à jour le client
            $clientService->updateAfterFacture(
                $client,
                $facture->montant_ttc,
                $facture->date_emission
            );

            DB::commit();

            return response()->json([
                'message' => 'Facture créée avec succès',
                'data' => new FactureResource($facture->load(['client', 'articles', 'commande']))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création de la facture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function show(Facture $facture): JsonResponse
{
    // Charger les relations liées à la facture :
    // - client : le client de la facture
    // - articles : les articles facturés
    // - commande : la commande associée
    $facture->load(['client', 'articles', 'commande']);

    // Retourner la facture formatée via FactureResource
    return response()->json([
        'data' => new FactureResource($facture)
    ]);
}


   public function update(UpdateFactureRequest $request, Facture $facture): JsonResponse
{
    try {
        // Démarrer une transaction pour éviter les incohérences
        DB::beginTransaction();

        // Récupérer toutes les données sauf les articles
        $updateData = $request->except('articles');

        // Vérifier si des articles sont envoyés
        if ($request->has('articles')) {

            // Calcul du montant hors taxe (HT)
            $montantHT = 0;
            foreach ($request->articles as $article) {
                $montantHT += $article['prix_unitaire'] * $article['quantite'];
            }

            // Mise à jour des montants de la facture
            $updateData['montant_ht'] = $montantHT;
            $updateData['tva'] = 0; // TVA à 0 (modifiable plus tard)
            $updateData['montant_ttc'] = $montantHT;

            // Supprimer tous les anciens articles liés à la facture
            $facture->articles()->delete();

            // Recréer les articles avec les nouvelles données
            foreach ($request->articles as $articleData) {
                // Calcul du total par article
                $articleData['total'] =
                    ($articleData['prix_unitaire'] ?? 0) *
                    ($articleData['quantite'] ?? 1);

                // Créer l’article lié à la facture
                $facture->articles()->create($articleData);
            }
        }

        // Mettre à jour la facture avec les nouvelles données
        $facture->update($updateData);

        // Valider la transaction
        DB::commit();

        // Retourner la facture mise à jour
        return response()->json([
            'message' => 'Facture modifiée avec succès',
            'data' => new FactureResource(
                $facture->load(['client', 'articles', 'commande'])
            )
        ]);

    } catch (\Exception $e) {

        // Annuler toutes les opérations en cas d’erreur
        DB::rollBack();

        // Retourner l’erreur
        return response()->json([
            'message' => 'Erreur lors de la modification',
            'error' => $e->getMessage()
        ], 500);
    }
}


  public function destroy(Facture $facture): JsonResponse
{
    // Supprimer la facture (soft delete si activé)
    $facture->delete();

    // Retourner un message de confirmation
    return response()->json([
        'message' => 'Facture supprimée'
    ]);
}


   public function marquerPayee(Request $request, Facture $facture): JsonResponse
{
    // Validation des données de paiement
    $request->validate([
        'montant_paye' => 'required|numeric|min:0|max:' . $facture->montant_ttc,
        'mode_paiement' => 'required|string',
        'date_paiement' => 'required|date',
    ]);

    // Mise à jour des informations de paiement
    $facture->update([
        'montant_paye' => $request->montant_paye,
        'mode_paiement' => $request->mode_paiement,
        'date_paiement' => $request->date_paiement,

        // Si le montant payé couvre le total → Payée, sinon → En attente
        'statut' => $request->montant_paye >= $facture->montant_ttc
            ? 'Payée'
            : 'En attente',
    ]);

    // Retourner la facture mise à jour
    return response()->json([
        'message' => 'Paiement enregistré',
        'data' => new FactureResource(
            $facture->load(['client', 'articles', 'commande'])
        )
    ]);
}


  public function stats(): JsonResponse
{
    // Récupérer toutes les factures
    $factures = Facture::all();

    // Compter les factures totalement payées
    $payees = $factures
        ->filter(fn($f) => $f->montant_paye >= $f->montant_ttc)
        ->count();

    // Calcul des statistiques globales
    $stats = [
        'total' => $factures->count(), // Nombre total de factures
        'chiffre_affaires' => $factures->sum('montant_ttc'), // CA total
        'payees' => $payees, // Factures payées
        'non_payees' => $factures->count() - $payees, // Factures non payées
        'encours' => $factures->sum(
            fn($f) => $f->montant_ttc - $f->montant_paye
        ), // Montant restant à encaisser
    ];

    // Retourner les statistiques
    return response()->json([
        'data' => $stats
    ]);
}

}