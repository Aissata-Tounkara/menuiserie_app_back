<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transforme l'article en tableau.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'reference' => $this->reference,
            'categorie' => $this->categorie,
            'quantite' => (int) $this->quantite,
            'unite' => $this->unite,
            'seuil_alerte' => (int) $this->seuil_alerte,
            'prix_achat' => (float) $this->prix_achat,
            'fournisseur' => $this->fournisseur,
            'emplacement' => $this->emplacement,
            'derniere_entree' => $this->derniere_entree?->format('d/m/Y'),
            'derniere_sortie' => $this->derniere_sortie?->format('d/m/Y'),
            'valeur_totale' => (float) $this->valeur_totale,
            'statut_stock' => $this->statut_stock,
            'en_alerte' => $this->isEnAlerte(),
            'critique' => $this->isCritique(),
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}