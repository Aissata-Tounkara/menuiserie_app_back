<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DevisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'date_emission' => $this->date_emission?->format('d/m/Y'),
            'validite' => $this->validite,
            'date_validite' => $this->date_validite?->format('d/m/Y'),
            'remise' => (float) $this->remise,
            'acompte' => (float) $this->acompte,
            'delai_livraison' => $this->delai_livraison,
            'conditions_paiement' => $this->conditions_paiement,
            'sous_total' => (float) $this->sous_total,
            'montant_remise' => (float) $this->montant_remise,
            'total_ht' => (float) $this->total_ht,
            'total_ttc' => (float) $this->total_ttc,
            'montant_acompte' => (float) $this->montant_acompte,
            'notes' => $this->notes,
            'statut' => $this->statut,
            'lignes' => LigneDevisResource::collection($this->whenLoaded('lignes')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
