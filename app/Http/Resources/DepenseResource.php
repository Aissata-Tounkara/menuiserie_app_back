<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'categorie' => $this->categorie,
            'description' => $this->description,
            'montant' => $this->montant,
            'date' => $this->date,
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}