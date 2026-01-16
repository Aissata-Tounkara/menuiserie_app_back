<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categorie' => ['sometimes', 'in:Achat matériaux,Transport,Électricité,Maintenance,Autre'],
            'description' => ['sometimes', 'string', 'max:500'],
            'montant' => ['sometimes', 'numeric', 'min:0'],
            'date' => ['sometimes', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'categorie.in' => 'La catégorie sélectionnée est invalide.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être positif.',
            'date.date' => 'La date doit être valide.',
        ];
    }
}