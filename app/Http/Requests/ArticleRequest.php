<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'reference' => ['required', 'string', 'max:100', 'unique:articles,reference,' . $this->route('article')?->id],
            'categorie' => ['required', 'string', 'max:100'],
            'quantite' => ['required', 'integer', 'min:0'],
            'unite' => ['required', 'string', 'max:50'],
            'seuil_alerte' => ['required', 'integer', 'min:0'],
            'prix_achat' => ['nullable', 'numeric', 'min:0'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
            'emplacement' => ['nullable', 'string', 'max:255'],
            'derniere_entree' => ['nullable', 'date'],
            'derniere_sortie' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l\'article est obligatoire.',
            'reference.required' => 'La référence est obligatoire.',
            'reference.unique' => 'Cette référence existe déjà.',
            'categorie.required' => 'La catégorie est obligatoire.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.min' => 'La quantité ne peut pas être négative.',
            'unite.required' => 'L\'unité est obligatoire.',
            'seuil_alerte.required' => 'Le seuil d\'alerte est obligatoire.',
            'seuil_alerte.min' => 'Le seuil d\'alerte ne peut pas être négatif.',
            'prix_achat.min' => 'Le prix d\'achat ne peut pas être négatif.',
        ];
    }
}