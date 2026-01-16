<?php

namespace App\Http\Controllers;


use App\Http\Requests\UpdateCommandeRequest;
use App\Http\Resources\CommandeResource;
use App\Models\Commande;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Commande::with(['client', 'articles']);

        if ($request->has('statut') && $request->statut !== 'Tous') {
            $query->where('statut', $request->statut);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_commande', 'like', "%{$search}%")
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
        $commandes = $query->paginate($perPage);

        return CommandeResource::collection($commandes);
    }

    // ❌ DÉSACTIVÉ : la commande est créée automatiquement depuis le devis
    // public function store(StoreCommandeRequest $request): JsonResponse { ... }

    public function show(Commande $commande): JsonResponse
    {
        $commande->load(['client', 'articles', 'devis']);
        return response()->json(['data' => new CommandeResource($commande)]);
    }

    public function update(UpdateCommandeRequest $request, Commande $commande): JsonResponse
    {
        try {
            DB::beginTransaction();

            $updateData = $request->except('articles');
            
            if ($request->has('articles')) {
                $montantHT = 0;
            foreach ($request->articles as $article) {
                $montantHT += $article['prix'] * $article['quantite'];
            }
                $updateData['montant_ht'] = $montantHT;
                $updateData['montant_ttc'] = $montantHT; // Pas de TVA

                $commande->articles()->delete();
                foreach ($request->articles as $articleData) {
                    $commande->articles()->create($articleData);
                }
            }

            $commande->update($updateData);
            DB::commit();

            return response()->json([
                'message' => 'Commande modifiée avec succès',
                'data' => new CommandeResource($commande->load(['client', 'articles']))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la modification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Commande $commande): JsonResponse
    {
        // ⚠️ Empêcher la suppression si une facture existe
        if ($commande->facture) {
            return response()->json([
                'message' => 'Impossible de supprimer une commande liée à une facture.'
            ], 400);
        }
        $commande->delete();
        return response()->json(['message' => 'Commande supprimée']);
    }

    public function updateStatut(Request $request, Commande $commande): JsonResponse
    {
        $request->validate([
            'statut' => 'required|in:En attente,En production,Prête,Livrée,Annulée'
        ]);

        $commande->update(['statut' => $request->statut]);
        return response()->json([
            'message' => 'Statut mis à jour',
            'data' => new CommandeResource($commande->load(['client', 'articles']))
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Commande::count(),
            'en_production' => Commande::where('statut', 'En production')->count(),
            'prete' => Commande::where('statut', 'Prête')->count(),
            'livree' => Commande::where('statut', 'Livrée')->count(),
            'en_attente' => Commande::where('statut', 'En attente')->count(),
            'annulee' => Commande::where('statut', 'Annulée')->count(),
        ];
        return response()->json(['data' => $stats]);
    }
}