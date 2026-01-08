<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    
    //   À quoi sert authorize() dans une FormRequest ?

    //     La méthode authorize() sert à dire à Laravel :

    //     “Est-ce que cet utilisateur a le droit de faire cette requête ?”

    //     C’est une couche de sécurité, séparée de la validation.

    //     Que se passe-t-il concrètement ?

    //     Quand une requête arrive :

    //     Laravel appelle authorize()

    //     Si ça retourne false 
    //     → Laravel bloque la requête AVANT la validation
    //     → Réponse automatique 403 Forbidden

    //     Si ça retourne true 
    //     → Laravel continue et applique les règles de rules()
     
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }
}
