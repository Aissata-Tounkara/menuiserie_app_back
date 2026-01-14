<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MouvementStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'article_id' => ['required', 'exists:articles,id'],
            'type' => ['required', 'in:entree,sortie'],
            'quantite' => ['required', 'integer', 'min:1'],
            'motif' => ['nullable', 'string', 'max:255'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
            'reference_document' => ['nullable', 'string', 'max:255'],
            'date_mouvement' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'article_id.required' => 'L\'article est obligatoire.',
            'article_id.exists' => 'L\'article sélectionné n\'existe pas.',
            'type.required' => 'Le type de mouvement est obligatoire.',
            'type.in' => 'Le type doit être "entree" ou "sortie".',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité doit être au minimum 1.',
            'date_mouvement.date' => 'La date du mouvement doit être valide.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('date_mouvement')) {
            $this->merge(['date_mouvement' => now()]);
        }
    }
}