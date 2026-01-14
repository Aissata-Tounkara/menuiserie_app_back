<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LigneDevisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'produit' => $this->produit,
            'categorie' => $this->categorie,
            'description' => $this->description,
            'largeur' => $this->largeur ? (float) $this->largeur : null,
            'hauteur' => $this->hauteur ? (float) $this->hauteur : null,
            'quantite' => $this->quantite,
            'alluminium' => $this->alluminium,
            'vitrage' => $this->vitrage,
            'prix_unitaire' => (float) $this->prix_unitaire,
            'sous_total' => (float) $this->sous_total,
            'ordre' => $this->ordre,
        ];
    }
}