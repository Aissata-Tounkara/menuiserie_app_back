<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'reference' => [
                'sometimes',
                'string',
                'max:100',
                'unique:articles,reference,' . $this->route('article')->id
            ],
            'categorie' => ['sometimes', 'string', 'max:100'],
            'quantite' => ['sometimes', 'integer', 'min:0'],
            'unite' => ['sometimes', 'string', 'max:50'],
            'seuil_alerte' => ['sometimes', 'integer', 'min:0'],
            'prix_achat' => ['nullable', 'numeric', 'min:0'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
            'emplacement' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference.unique' => 'Cette référence existe déjà.',
            'quantite.min' => 'La quantité ne peut pas être négative.',
            'seuil_alerte.min' => 'Le seuil d\'alerte ne peut pas être négatif.',
            'prix_achat.min' => 'Le prix d\'achat ne peut pas être négatif.',
        ];
    }
}