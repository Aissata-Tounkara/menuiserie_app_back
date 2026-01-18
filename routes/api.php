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

/*
|--------------------------------------------------------------------------
| AUTHENTIFICATION
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPasswordWithCode']);
});

Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // ──────────────── DASHBOARD ────────────────
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData']);

    // ──────────────── DEVIS ────────────────
    Route::post('devis/{devis}/valider', [DevisController::class, 'validerEtFacturer']);
    Route::apiResource('devis', DevisController::class);

    // ──────────────── COMMANDES ────────────────
    Route::get('commandes/stats', [CommandeController::class, 'stats']);
    Route::post('commandes/{commande}/statut', [CommandeController::class, 'updateStatut']);
    Route::apiResource('commandes', CommandeController::class);

    // ──────────────── FACTURES ────────────────
    Route::get('factures/stats', [FactureController::class, 'stats']);
    Route::post('factures/{facture}/payer', [FactureController::class, 'marquerPayee']);
    Route::apiResource('factures', FactureController::class);


    // ──────────────── CLIENTS ────────────────
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'store']);
        Route::get('/stats', [ClientController::class, 'stats']);
        Route::get('/{client}', [ClientController::class, 'show']);
        Route::put('/{client}', [ClientController::class, 'update']);
        Route::delete('/{client}', [ClientController::class, 'destroy']);
        Route::patch('/{client}/statut', [ClientController::class, 'updateStatut']);
    });

    // ──────────────── DÉPENSES ────────────────
    Route::get('depenses/stats', [DepenseController::class, 'stats']);
    Route::apiResource('depenses', DepenseController::class);

    // ──────────────── ARTICLES ────────────────
    Route::get('articles/stats', [ArticleController::class, 'stats']);
    Route::get('articles/alertes', [ArticleController::class, 'alertes']);
    Route::post('articles/{article}/ajuster-stock', [ArticleController::class, 'ajusterStock']);
    Route::apiResource('articles', ArticleController::class);

    // ──────────────── MOUVEMENT STOCK ────────────────
    Route::get('mouvement/stats', [MouvementController::class, 'stats']);
    Route::get('articles/{article}/historique-mouvement', [MouvementController::class, 'historique']);
    Route::post('mouvement', [MouvementController::class, 'store']);
    Route::apiResource('mouvement', MouvementController::class)->except(['store']);

});
    Route::get('/factures/{id}/telecharger-pdf', [FactureController::class, 'telechargerPDF']);
