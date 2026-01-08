<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordWithCodeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    // Envoyer un code à 6 chiffres par e-mail
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $email = $request->email;

        // Générer un code à 6 chiffres
        $code = rand(100000, 999999);

        // Sauvegarder en BDD avec expiration (15 min)
        DB::table('password_reset_codes')->updateOrInsert(
            ['email' => $email],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(5),
            ]
        );

        // Envoyer par e-mail via Brevo
        Mail::raw("Votre code de réinitialisation est : {$code}", function ($message) use ($email) {
            $message->to($email)
                    ->subject('Réinitialisation de mot de passe - Menuiserie');
        });

        return response()->json(['message' => 'Un code à 6 chiffres a été envoyé à votre e-mail.']);
    }

    // Réinitialiser avec le code
    public function resetPasswordWithCode(ResetPasswordWithCodeRequest $request)
    {
        $record = DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Code invalide ou expiré'], 422);
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $request->email)->first();
        $user->password = $request->password; // Haché automatiquement par le mutator
        $user->save();

        // Supprimer le code utilisé
        DB::table('password_reset_codes')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
    }

    // Récupérer les infos de l'utilisateur connecté
    public function me(Request $request) {
        return response()->json($request->user());
    }
}