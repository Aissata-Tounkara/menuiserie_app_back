<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFactureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_emission' => 'sometimes|date',
            'date_echeance' => 'sometimes|date|after_or_equal:date_emission',
            'montant_paye' => 'sometimes|numeric|min:0',
            'statut' => 'sometimes|in:Non payée,Payée,En attente,En retard',
            'mode_paiement' => 'nullable|string|max:100',
            'date_paiement' => 'nullable|date',
            'notes' => 'nullable|string',
            'articles' => 'sometimes|array|min:1',
            'articles.*.designation' => 'required_with:articles|string|max:255',
            'articles.*.quantite' => 'required_with:articles|integer|min:1',
            'articles.*.prix_unitaire' => 'required_with:articles|numeric|min:0',
        ];
    }
}