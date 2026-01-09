<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'devis_id' => $this->devis_id,
            'nom' => $this->nom,
            'statut' => $this->statut,
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin,
            'note' => $this->note,
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}