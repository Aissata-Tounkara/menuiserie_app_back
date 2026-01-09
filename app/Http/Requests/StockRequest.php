<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'unite' => 'required|in:m,m²,unite',
            'quantite' => 'required|numeric|min:0',
            'seuil_alerte' => 'required|numeric|min:0',
            'prix_unitaire' => 'nullable|numeric|min:0',
        ];
    }
}