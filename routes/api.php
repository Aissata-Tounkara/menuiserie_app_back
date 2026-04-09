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
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\ActivityLogger;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SessionController;

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
| ROUTES ADMIN (réservées aux administrateurs)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function ()  {
    
    Route::get('activities', [ActivityLogController::class, 'index']);
    Route::get('activities/{activity}', [ActivityLogController::class, 'show']);
    Route::delete('activities/{activity}', [ActivityLogController::class, 'destroy']);
    
    Route::apiResource('users', UserManagementController::class)->except(['create', 'edit']);
    Route::patch('users/{user}/role', [UserManagementController::class, 'updateRole']);
    
    Route::get('sessions', [SessionController::class, 'index']);
    Route::delete('sessions/{sessionId}', [SessionController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', ActivityLogger::class])->group(function () {

    // ──────────────── DASHBOARD ────────────────
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData']);

    // ──────────────── DEVIS ────────────────
    Route::post('devis/{devis}/valider', [DevisController::class, 'validerEtFacturer']);
    Route::apiResource('devis', DevisController::class);
    // routes/api.php
    // ──────────────── COMMANDES ────────────────
    Route::get('commandes/stats', [CommandeController::class, 'stats']);
    Route::post('commandes/{commande}/statut', [CommandeController::class, 'updateStatut']);
    Route::apiResource('commandes', CommandeController::class);

    // ──────────────── FACTURES ────────────────
    Route::get('factures/stats', [FactureController::class, 'stats']);
    Route::post('factures/{facture}/payer', [FactureController::class, 'marquerPayee']);
    // === 🆕 NOUVEAU : Routes pour les paiements d'une facture ===
    Route::prefix('factures/{facture}/paiements')->group(function () {
        Route::get('/', [\App\Http\Controllers\PaiementController::class, 'index']);           // Liste
        Route::post('/', [\App\Http\Controllers\PaiementController::class, 'store']);          // Créer
        Route::get('/{paiement}', [\App\Http\Controllers\PaiementController::class, 'show']);  // Détails
        Route::put('/{paiement}', [\App\Http\Controllers\PaiementController::class, 'update']); // Modifier
        Route::delete('/{paiement}', [\App\Http\Controllers\PaiementController::class, 'destroy']); // Supprimer
    });
    Route::apiResource('factures', FactureController::class);
     Route::get('factures/{facture}/telecharger-pdf', [FactureController::class, 'telechargerPDF']);


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

    // // ──────────────── PDF du facture  ────────────────
    //   Route::get('/factures/{id}/telecharger-pdf', [FactureController::class, 'telechargerPDF']);


});
  