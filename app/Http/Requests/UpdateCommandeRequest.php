<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'sometimes|exists:clients,id',
            'date_commande' => 'sometimes|date',
            'date_livraison' => 'nullable|date|after_or_equal:date_commande',
            'statut' => 'sometimes|in:En attente,En production,Prête,Livrée,Annulée',
            'notes' => 'nullable|string',
            'articles' => 'sometimes|array|min:1',
            'articles.*.produit' => 'required_with:articles|string|max:255',
            'articles.*.quantite' => 'required_with:articles|integer|min:1',
            'articles.*.dimensions' => 'nullable|string|max:100',
            'articles.*.prix' => 'required_with:articles|numeric|min:0',
        ];
    }
}