<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'prenom' => ['sometimes', 'required', 'string', 'max:255'],
            'telephone' => ['sometimes', 'required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['sometimes', 'required', 'string'],
            'ville' => ['sometimes', 'required', 'string', 'max:255'],
            'code_postal' => ['nullable', 'string', 'max:10'],
            'type_client' => ['sometimes', 'required', 'in:Particulier,Professionnel'],
            'statut' => ['nullable', 'in:Actif,Inactif,VIP'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'telephone.required' => 'Le téléphone est obligatoire.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'type_client.required' => 'Le type de client est obligatoire.',
        ];
    }
}