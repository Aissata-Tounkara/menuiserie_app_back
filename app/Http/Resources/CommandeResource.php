<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_commande' => $this->numero_commande,
            'client' => new ClientResource($this->whenLoaded('client')),
            'devis_id' => $this->devis_id,
            'date_commande' => $this->date_commande?->format('d/m/Y'),
            'date_livraison' => $this->date_livraison?->format('d/m/Y'),
            'statut' => $this->statut,
            'montant_ht' => (float) $this->montant_ht,
            'montant_ttc' => (float) $this->montant_ttc,
            'notes' => $this->notes,
            'articles' => ArticleCommandeResource::collection($this->whenLoaded('articles')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
