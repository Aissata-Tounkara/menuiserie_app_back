<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client');

        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['required', 'string'],
            'ville' => ['required', 'string', 'max:255'],
            'code_postal' => ['nullable', 'string', 'max:10'],
            'type_client' => ['required', 'in:Particulier,Professionnel'],
            'date_inscription' => ['nullable', 'date'],
            'nombre_commandes' => ['nullable', 'integer', 'min:0'],
            'total_achats' => ['nullable', 'numeric', 'min:0'],
            'derniere_commande' => ['nullable', 'date'],
            'statut' => ['nullable', 'in:Actif,Inactif,VIP'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'telephone.required' => 'Le téléphone est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'ville.required' => 'La ville est obligatoire.',
            'type_client.required' => 'Le type de client est obligatoire.',
            'type_client.in' => 'Le type de client doit être Particulier ou Professionnel.',
            'statut.in' => 'Le statut doit être Actif, Inactif ou VIP.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('date_inscription')) {
            $this->merge([
                'date_inscription' => now()->format('Y-m-d'),
            ]);
        }

        if (!$this->has('statut')) {
            $this->merge([
                'statut' => 'Actif',
            ]);
        }
    }
}