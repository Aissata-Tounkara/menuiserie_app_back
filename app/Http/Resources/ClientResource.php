<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'nom_complet' => $this->nom_complet,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            // APRÈS
            'codePostal' => $this->code_postal,
            'typeClient' => $this->type_client,
            'dateInscription' => $this->date_inscription?->format('d/m/Y'),
            'nombreCommandes' => $this->nombre_commandes,
            'derniereCommande' => $this->derniere_commande?->format('d/m/Y'),
             'total_achats' => (float) $this->total_achats, // ✅ Ajouté ici
            'createdAt' => $this->created_at?->format('d/m/Y H:i'),
            'updatedAt' => $this->updated_at?->format('d/m/Y H:i'),
            'statut' => $this->statut,
            
        ];
    }
}