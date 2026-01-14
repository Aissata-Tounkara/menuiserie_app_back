<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDevisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'sometimes|exists:clients,id',
            'date_emission' => 'sometimes|date',
            'validite' => 'sometimes|integer|min:1',
            'remise' => 'nullable|numeric|min:0|max:100',
            'acompte' => 'nullable|numeric|min:0|max:100',
            'delai_livraison' => 'nullable|string|max:255',
            'conditions_paiement' => 'nullable|string',
            'notes' => 'nullable|string',
            'statut' => 'sometimes|in:brouillon,envoye,accepte,refuse,expire',
            'lignes' => 'sometimes|array|min:1',
            'lignes.*.produit' => 'required_with:lignes|string|max:255',
            'lignes.*.categorie' => 'nullable|string|max:100',
            'lignes.*.description' => 'nullable|string',
            'lignes.*.largeur' => 'nullable|numeric|min:0',
            'lignes.*.hauteur' => 'nullable|numeric|min:0',
            'lignes.*.quantite' => 'required_with:lignes|integer|min:1',
            'lignes.*.aluminium' => 'nullable|string|max:100',
            'lignes.*.vitrage' => 'nullable|string|max:100',
            // ❌ Supprimé : 'lignes.*.prix_unitaire'
        ];
    }
}