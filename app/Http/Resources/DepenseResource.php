<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepenseResource extends JsonResource
{
    /**
     * Transforme le modèle en tableau pour la réponse JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'categorie' => $this->categorie,
            'description' => $this->description,
            'montant' => (float) $this->montant,
            'montant_formate' => number_format($this->montant, 0, ',', ' ') . ' FCFA', // Format lisible : "125 000 FCFA"
            'date' => $this->date?->format('Y-m-d'),
            'date_formatee' => $this->date?->format('d/m/Y'), // Pour affichage utilisateur
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
            'deleted_at' => $this->deleted_at?->format('d/m/Y H:i'), // utile si soft-deleted
        ];
    }
}