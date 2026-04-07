<?php
// app/Http/Resources/PaiementResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'facture_id' => $this->facture_id,
            'montant' => (float) $this->montant,
            'date_paiement' => $this->date_paiement?->format('d/m/Y'),
            'mode_paiement' => $this->mode_paiement,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}