<?php

// app/Http/Resources/FactureResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_facture' => $this->numero_facture,
            'commande' => $this->commande?->numero_commande,
            'commande_id' => $this->commande_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'date_emission' => $this->date_emission?->format('d/m/Y'),
            'date_echeance' => $this->date_echeance?->format('d/m/Y'),
            'montant_ht' => (float) $this->montant_ht,
            'tva' => (float) $this->tva,
            'montant_ttc' => (float) $this->montant_ttc,
            'montant_paye' => (float) $this->montant_paye,
            'statut' => $this->statut,
            'statut_calcule' => $this->statut_calcule,
            'mode_paiement' => $this->mode_paiement,
            'date_paiement' => $this->date_paiement?->format('d/m/Y'),
            'notes' => $this->notes,
            'articles' => ArticleFactureResource::collection($this->whenLoaded('articles')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}