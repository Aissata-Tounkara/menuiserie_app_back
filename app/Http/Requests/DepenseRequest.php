<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
           'categorie' => ['sometimes', 'in:Achat matériaux,Transport,Électricité,Maintenance,Autre'],
            'description' => ['required', 'string', 'max:500'],
            'montant' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date', 'before_or_equal:today'],

        ];
    }

    public function messages(): array
    {
        return [
            'categorie.required' => 'La catégorie est obligatoire.',
            'categorie.in' => 'La catégorie sélectionnée est invalide.',
            'description.required' => 'La description est obligatoire.',
            'description.max' => 'La description ne peut pas dépasser 500 caractères.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être positif.',
            'date.required' => 'La date est obligatoire.',
            'date.date' => 'La date doit être valide.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('date')) {
            $this->merge([
                'date' => now()->format('Y-m-d'),
            ]);
        }
    }
}