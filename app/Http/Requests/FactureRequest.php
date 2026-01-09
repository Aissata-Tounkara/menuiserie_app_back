<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FactureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'devis_id' => 'required|exists:devis,id',
            'montant_total' => 'required|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0|lte:montant_total',
            'statut_paiement' => 'required|in:non_paye,partiel,paye',
        ];
    }
}