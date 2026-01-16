<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'devis_id' => 'required|exists:devis,id',
            'nom' => 'required|string|max:255',
            'statut' => 'required|in:en_attente,en_cours,termine,livre',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'note' => 'nullable|string',
        ];
    }
}