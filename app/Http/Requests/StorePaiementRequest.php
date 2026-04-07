<?php
// app/Http/Requests/StorePaiementRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'montant' => ['required', 'numeric', 'min:0.01'],
            'date_paiement' => ['required', 'date', 'before_or_equal:today'],
            'mode_paiement' => ['required', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'montant.required' => 'Le montant du paiement est requis.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
            'date_paiement.before_or_equal' => 'La date de paiement ne peut pas être dans le futur.',
            'mode_paiement.required' => 'Le mode de paiement est requis.',
        ];
    }
}