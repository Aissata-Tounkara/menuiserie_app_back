<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleCommandeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'produit' => $this->produit,
            'quantite' => $this->quantite,
            'dimensions' => $this->dimensions,
            'prix' => (float) $this->prix,
        ];
    }
}
