<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MouvementStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'article' => $this->whenLoaded('article', [
                'id' => $this->article->id,
                'nom' => $this->article->nom,
                'reference' => $this->article->reference,
            ]),
            'type' => $this->type,
            'quantite' => (int) $this->quantite,
            'quantite_avant' => (int) $this->quantite_avant,
            'quantite_apres' => (int) $this->quantite_apres,
            'motif' => $this->motif,
            'commentaire' => $this->commentaire,
            'reference_document' => $this->reference_document,
            'user' => $this->whenLoaded('user', fn() => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
            'date_mouvement' => $this->date_mouvement?->format('d/m/Y H:i'),
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}