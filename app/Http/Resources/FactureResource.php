<?php

// app/Http/Resources/FactureResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactureResource extends JsonResource
{
  // app/Http/Resources/FactureResource.php - À METTRE À JOUR

public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'numero_facture' => $this->numero_facture,
        'commande' => $this->commande?->numero_commande,
        'commande_id' => $this->commande_id,
        'client' => new ClientResource($this->whenLoaded('client')),
        'date_emission' => $this->date_emission?->format('d/m/Y'),
        'date_facture' => $this->date_emission?->format('d/m/Y'),
        'date_echeance' => $this->date_echeance?->format('d/m/Y'),
        
        // 💰 CHAMPS FINANCIERS CALCULÉS
        'montant_ht' => (float) $this->montant_ht,
        'tva' => (float) $this->tva,
        'montant_ttc' => (float) $this->montant_ttc,
        
        // ✅ NOUVEAUX CHAMPS BASÉS SUR LES PAIEMENTS
        'total_paye' => (float) $this->total_paye,
        'reste_a_payer' => (float) $this->reste_a_payer,
        'statut_calcule' => $this->statut_calcule, // Remplace l'ancien statut
        
        // Anciens champs (gardés pour rétrocompatibilité)
        'statut' => $this->statut,
        'montant_paye' => (float) $this->total_paye, // Map vers le nouveau calcul
        
        'mode_paiement' => $this->mode_paiement, // Dernier mode de paiement
        'date_paiement' => $this->date_paiement?->format('d/m/Y'),
        'notes' => $this->notes,
        
        // Relations
        'articles' => ArticleFactureResource::collection($this->whenLoaded('articles')),
        'paiements' => PaiementResource::collection($this->whenLoaded('paiements')),
        
        'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
    ];
}
}
