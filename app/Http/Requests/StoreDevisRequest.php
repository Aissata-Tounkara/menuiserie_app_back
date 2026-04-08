<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDevisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'date_emission' => 'required|date',
            'validite' => 'required|integer|min:1',
            'remise' => 'nullable|numeric|min:0|max:100',
            'acompte' => 'nullable|numeric|min:0|max:100',
            'delai_livraison' => 'nullable|string|max:255',
            'conditions_paiement' => 'nullable|string',
            'notes' => 'nullable|string',
            'lignes' => 'required|array|min:1',
            'lignes.*.produit' => 'required|string|max:255',
            'lignes.*.categorie' => 'nullable|string|max:100',
            'lignes.*.description' => 'nullable|string',
            'lignes.*.largeur' => 'required_with:lignes.*.hauteur|nullable|numeric|gt:0',
            'lignes.*.hauteur' => 'required_with:lignes.*.largeur|nullable|numeric|gt:0',
            'lignes.*.quantite' => 'required|integer|min:1',
            'lignes.*.aluminium' => 'nullable|string|max:100',
            'lignes.*.vitrage' => 'nullable|string|max:100',
            // ❌ Supprimé : 'lignes.*.prix_unitaire'
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'date_emission.required' => 'La date d\'émission est obligatoire.',
            'lignes.required' => 'Au moins un article est requis.',
            'lignes.*.produit.required' => 'Le produit est obligatoire.',
            'lignes.*.largeur.required_with' => 'La largeur est requise si une hauteur est fournie.',
            'lignes.*.hauteur.required_with' => 'La hauteur est requise si une largeur est fournie.',
            'lignes.*.largeur.gt' => 'La largeur doit être supérieure à 0.',
            'lignes.*.hauteur.gt' => 'La hauteur doit être supérieure à 0.',
            'lignes.*.quantite.required' => 'La quantité est obligatoire.',
        ];
    }
}
