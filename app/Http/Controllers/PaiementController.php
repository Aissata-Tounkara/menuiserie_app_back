<?php
// app/Http/Controllers/PaiementController.php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaiementRequest;
use App\Http\Resources\PaiementResource;
use App\Models\Facture;
use App\Models\Paiement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PaiementController extends Controller
{
    /**
     * Liste des paiements d'une facture
     */
    public function index(Facture $facture): AnonymousResourceCollection
    {
        $paiements = $facture->paiements()
            ->orderBy('date_paiement', 'desc')
            ->paginate(15);
            
        return PaiementResource::collection($paiements);
    }

    /**
     * Store a newly created payment
     */
    public function store(StorePaiementRequest $request, Facture $facture): JsonResponse
    {
        try {
            DB::beginTransaction();

            // 1. Créer le paiement
            $paiement = $facture->paiements()->create([
                'montant' => $request->montant,
                'date_paiement' => $request->date_paiement,
                'mode_paiement' => $request->mode_paiement,
                'reference' => $request->reference,
                'notes' => $request->notes,
            ]);

            // 2. Refresh automatique du statut de la facture
            $facture->refreshStatut();

            // 3. Recharger la facture avec les nouvelles données
            $facture->load(['paiements', 'client', 'articles']);

            DB::commit();

            return response()->json([
                'message' => 'Paiement enregistré avec succès',
                'data' => [
                    'paiement' => new PaiementResource($paiement),
                    'facture' => [
                        'total_paye' => (float) $facture->total_paye,
                        'reste_a_payer' => (float) $facture->reste_a_payer,
                        'statut' => $facture->statut_calcule,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de l\'enregistrement du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un paiement spécifique
     */
    public function show(Facture $facture, Paiement $paiement): JsonResponse
    {
        // Vérifier que le paiement appartient à la facture
        if ($paiement->facture_id !== $facture->id) {
            return response()->json(['message' => 'Paiement non trouvé'], 404);
        }
        
        return response()->json(['data' => new PaiementResource($paiement)]);
    }

    /**
     * Mettre à jour un paiement
     */
    public function update(StorePaiementRequest $request, Facture $facture, Paiement $paiement): JsonResponse
    {
        if ($paiement->facture_id !== $facture->id) {
            return response()->json(['message' => 'Paiement non trouvé'], 404);
        }

        try {
            DB::beginTransaction();

            $paiement->update($request->only([
                'montant', 'date_paiement', 'mode_paiement', 'reference', 'notes'
            ]));

            // Refresh du statut de la facture
            $facture->refreshStatut();
            $facture->load(['paiements', 'client', 'articles']);

            DB::commit();

            return response()->json([
                'message' => 'Paiement modifié avec succès',
                'data' => [
                    'paiement' => new PaiementResource($paiement),
                    'facture' => [
                        'total_paye' => (float) $facture->total_paye,
                        'reste_a_payer' => (float) $facture->reste_a_payer,
                        'statut' => $facture->statut_calcule,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la modification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un paiement
     */
    public function destroy(Facture $facture, Paiement $paiement): JsonResponse
    {
        if ($paiement->facture_id !== $facture->id) {
            return response()->json(['message' => 'Paiement non trouvé'], 404);
        }

        try {
            DB::beginTransaction();
            
            $paiement->delete();
            
            // Refresh du statut après suppression
            $facture->refreshStatut();
            
            DB::commit();

            return response()->json([
                'message' => 'Paiement supprimé',
                'facture' => [
                    'total_paye' => (float) $facture->total_paye,
                    'reste_a_payer' => (float) $facture->reste_a_payer,
                    'statut' => $facture->statut_calcule,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}