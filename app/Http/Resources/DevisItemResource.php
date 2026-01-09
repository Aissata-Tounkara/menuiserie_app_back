<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DevisItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type_produit' => $this->type_produit,
            'description' => $this->description,
            'unite' => $this->unite,
            'largeur' => $this->largeur,
            'hauteur' => $this->hauteur,
            'longueur' => $this->longueur,
            'quantite' => $this->quantite,
            'prix_unitaire' => $this->prix_unitaire,
            'total' => $this->total,
        ];
    }
}