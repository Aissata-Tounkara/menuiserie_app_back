<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DevisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'statut' => 'required|in:brouillon,valide,rejete',
            'items' => 'required|array|min:1',
            'items.*.type_produit' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.unite' => 'required|in:m²,ml,unite',
            'items.*.quantite' => 'required|integer|min:1',
            'items.*.prix_unitaire' => 'required|numeric|min:0',
            'items.*.largeur' => 'required_if:items.*.unite,m²|nullable|numeric|min:0',
            'items.*.hauteur' => 'required_if:items.*.unite,m²|nullable|numeric|min:0',
            'items.*.longueur' => 'required_if:items.*.unite,ml|nullable|numeric|min:0',
        ];
    }
}