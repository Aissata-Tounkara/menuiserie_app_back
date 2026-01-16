<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DevisController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\FactureController; 
use App\Http\Controllers\ClientController;

use App\Http\Controllers\DepenseController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\MouvementController;
use App\Http\Controllers\DashboardController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPasswordWithCode']);
});

// Routes protégées : middleware auth:sanctum
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
     Route::get('/me', [AuthController::class, 'me']);
});





/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentification (si tu l'ajoutes plus tard)
// Route::middleware('auth:sanctum')->group(function () {

// ──────────────── DEVIS ────────────────
Route::apiResource('devis', DevisController::class);
Route::post('devis/{devis}/valider', [DevisController::class, 'validerEtFacturer']);

// ──────────────── COMMANDES ────────────────
Route::apiResource('commandes', CommandeController::class);
Route::post('commandes/{commande}/statut', [CommandeController::class, 'updateStatut']);
Route::get('commandes/stats', [CommandeController::class, 'stats']);

// ──────────────── FACTURES ────────────────
Route::apiResource('factures', FactureController::class);
Route::post('factures/{facture}/payer', [FactureController::class, 'marquerPayee']);
Route::get('factures/stats', [FactureController::class, 'stats']);

// });

Route::prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    Route::post('/', [ClientController::class, 'store']);
    Route::get('/stats', [ClientController::class, 'stats']);
    Route::get('/{client}', [ClientController::class, 'show']);
    Route::put('/{client}', [ClientController::class, 'update']);
    Route::delete('/{client}', [ClientController::class, 'destroy']);
    Route::patch('/{client}/statut', [ClientController::class, 'updateStatut']);
});

Route::get('depenses/stats', [DepenseController::class, 'stats']);
// Route d'export CSV optionnelle (mais tu l’as côté frontend → inutile ici)
// Gestion des dépenses
Route::apiResource('depenses', DepenseController::class);


// Articles
// Routes spécifiques AVANT apiResource
Route::get('articles/stats', [ArticleController::class, 'stats']);
Route::get('articles/alertes', [ArticleController::class, 'alertes']);
Route::post('articles/{article}/ajuster-stock', [ArticleController::class, 'ajusterStock']);
// Route générique en dernier
Route::apiResource('articles', ArticleController::class);

// Mouvements de stock
// Routes spécifiques AVANT apiResource
Route::get('mouvement/stats', [MouvementController::class, 'stats']);
Route::get('articles/{article}/historique-mouvement', [MouvementController::class, 'historique']);

// Route générique en dernier
Route::apiResource('mouvement', MouvementController::class)->except(['store']);
Route::post('mouvement', [MouvementController::class, 'store']);

 // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData']);