<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleFactureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'designation' => $this->designation,
            'quantite' => $this->quantite,
            'prix_unitaire' => (float) $this->prix_unitaire,
            'total' => (float) $this->total,
        ];
    }
}
