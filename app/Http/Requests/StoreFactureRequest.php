<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFactureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ⚠️ commande_id OBLIGATOIRE (pas de facture sans commande)
            'commande_id' => ['required', 'exists:commandes,id'],

            // Dates
            'date_emission' => ['required', 'date', 'before_or_equal:today'],
            'date_echeance' => ['required', 'date', 'after_or_equal:date_emission'],

            // Montants
            'montant_ht' => ['required', 'numeric', 'min:0'],
            'tva' => ['nullable', 'numeric', 'min:0'],
            'montant_ttc' => ['required', 'numeric', 'min:0', 'gte:montant_ht'],

            // Paiement initial
            'montant_paye' => ['nullable', 'numeric', 'min:0', 'lte:montant_ttc'],
            'mode_paiement' => ['nullable', 'string', 'max:100'],
            'date_paiement' => ['nullable', 'date', 'before_or_equal:today'],

            // Statut
            'statut' => ['required', 'in:Non payée,Payée,En attente,En retard'],

            // Notes
            'notes' => ['nullable', 'string', 'max:1000'],

            // Articles
            'articles' => ['nullable', 'array'],
            'articles.*.designation' => ['required_with:articles', 'string', 'max:255'],
            'articles.*.quantite' => ['required_with:articles', 'integer', 'min:1'],
            'articles.*.prix_unitaire' => ['required_with:articles', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'commande_id.required' => 'La facture doit être liée à une commande.',
            'commande_id.exists' => 'La commande sélectionnée est invalide.',
            'date_emission.before_or_equal' => 'La date d’émission ne peut pas être dans le futur.',
            'date_echeance.after_or_equal' => 'La date d’échéance doit être égale ou postérieure à la date d’émission.',
            'montant_ttc.gte' => 'Le montant TTC doit être supérieur ou égal au montant HT.',
            'montant_paye.lte' => 'Le montant payé ne peut pas dépasser le montant total.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('tva')) {
            $this->merge(['tva' => 0]);
        }
        if (!$this->has('montant_paye')) {
            $this->merge(['montant_paye' => 0]);
        }
        if (!$this->has('statut')) {
            $this->merge(['statut' => 'Non payée']);
        }
    }
}