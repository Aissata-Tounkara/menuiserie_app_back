<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'devis_id' => $this->devis_id,
            'montant_total' => $this->montant_total,
            'montant_paye' => $this->montant_paye,
            'reste_a_payer' => $this->montant_total - $this->montant_paye,
            'statut_paiement' => $this->statut_paiement,
            'fichier_pdf' => $this->fichier_pdf,
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}