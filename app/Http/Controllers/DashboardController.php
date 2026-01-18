<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Devis;
use App\Models\Depense;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Période par défaut : mois en cours
        $periode = $request->input('periode', 'mois');

        // Définir les dates selon la période
        $dateDebut = match ($periode) {
            'semaine' => now()->startOfWeek(),
            'trimestre' => now()->startOfQuarter(),
            'annee' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $dateFin = now();

        // === STATS PRINCIPALES (selon la période) ===

        // 1. Nombre de commandes dans la période
        $nombreCommandes = Commande::whereBetween('date_commande', [$dateDebut, $dateFin])
            ->whereNull('deleted_at')
            ->count();

        // 2. Revenus (total des commandes dans la période)
        $revenus = Commande::whereBetween('date_commande', [$dateDebut, $dateFin])
            ->whereNull('deleted_at')
            ->sum('montant_ttc');

        // 3. Clients actifs (ayant des commandes En production ou Prête dans la période)
        $clientsActifs = Client::whereHas('commandes', function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_commande', [$dateDebut, $dateFin])
                    ->whereIn('statut', ['En production', 'Prête'])
                    ->whereNull('commandes.deleted_at');
            })
            ->whereNull('clients.deleted_at')
            ->distinct()
            ->count('clients.id');

        // 4. Nombre total de produits (articles)
        $nombreProduits = Article::whereNull('deleted_at')->count();

        // === DÉTAILS DES COMMANDES ===
        $statsCommandes = [
            'total' => $nombreCommandes,
            'en_attente' => Commande::whereBetween('date_commande', [$dateDebut, $dateFin])
                ->where('statut', 'En attente')
                ->whereNull('deleted_at')
                ->count(),
            'en_production' => Commande::whereBetween('date_commande', [$dateDebut, $dateFin])
                ->where('statut', 'En production')
                ->whereNull('deleted_at')
                ->count(),
            'prete' => Commande::whereBetween('date_commande', [$dateDebut, $dateFin])
                ->where('statut', 'Prête')
                ->whereNull('deleted_at')
                ->count(),
            'livrees' => Commande::whereBetween('date_commande', [$dateDebut, $dateFin])
                ->where('statut', 'Livrée')
                ->whereNull('deleted_at')
                ->count(),
            'annulees' => Commande::whereBetween('date_commande', [$dateDebut, $dateFin])
                ->where('statut', 'Annulée')
                ->whereNull('deleted_at')
                ->count(),
        ];

        // === 10 COMMANDES RÉCENTES ===
        $commandesRecentes = Commande::with('client')
            ->whereNull('commandes.deleted_at')
            ->orderBy('date_commande', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($commande) {
                return [
                    'id' => $commande->id,
                    'numero_commande' => $commande->numero_commande,
                    'client' => $commande->client ? ($commande->client->nom . ' ' . $commande->client->prenom) : 'Client supprimé',
                    'client_id' => $commande->client_id,
                    'montant_ttc' => (float) $commande->montant_ttc,
                    'montant_ht' => (float) $commande->montant_ht,
                    'statut' => $commande->statut,
                    'date_commande' => $commande->date_commande ? $commande->date_commande->format('d/m/Y') : null,
                    'date_livraison' => $commande->date_livraison ? $commande->date_livraison->format('d/m/Y') : null,
                ];
            });

        // === TOP ARTICLES CONSOMMÉS (optionnel) ===
        $topArticles = DB::table('articles')
            ->leftJoin('mouvements_stock', function ($join) use ($dateDebut, $dateFin) {
                $join->on('articles.id', '=', 'mouvements_stock.article_id')
                    ->where('mouvements_stock.type', '=', 'sortie')
                    ->whereBetween('mouvements_stock.date_mouvement', [$dateDebut, $dateFin]);
            })
            ->whereNull('articles.deleted_at')
            ->select('articles.nom', 'articles.reference', DB::raw('COALESCE(SUM(mouvements_stock.quantite), 0) as total_sorties'))
            ->groupBy('articles.id', 'articles.nom', 'articles.reference')
            ->orderByDesc('total_sorties')
            ->limit(5)
            ->get()
            ->map(function ($article) {
                return [
                    'nom' => $article->nom,
                    'reference' => $article->reference,
                    'quantite_sortie' => (int) $article->total_sorties,
                ];
            });

        // === ALERTES ===
        $alertes = [
            'stock_faible' => Article::where('quantite', '<=', DB::raw('seuil_alerte'))
                ->where('quantite', '>', 0)
                ->whereNull('deleted_at')
                ->count(),
            'stock_critique' => Article::where('quantite', '=', 0)
                ->whereNull('deleted_at')
                ->count(),
            'devis_en_attente' => Devis::where('statut', 'brouillon')
                ->orWhere('statut', 'envoye')
                ->whereNull('deleted_at')
                ->count(),
            'factures_impayees' => Facture::whereIn('statut', ['Non payée', 'En retard'])
                ->whereNull('deleted_at')
                ->count(),
            'livraisons_du_jour' => Commande::whereDate('date_livraison', now()->format('Y-m-d'))
                ->whereIn('statut', ['Prête', 'En production'])
                ->whereNull('deleted_at')
                ->count(),
        ];

        return response()->json([
            'stats' => [
                'commandes' => $nombreCommandes,
                'revenus' => (float) $revenus,
                'clients_actifs' => $clientsActifs,
                'produits' => $nombreProduits,
            ],
            'details_commandes' => $statsCommandes,
            'commandes_recentes' => $commandesRecentes,
            'top_articles' => $topArticles,
            'alertes' => $alertes,
            'periode' => [
                'type' => $periode,
                'date_debut' => $dateDebut->format('Y-m-d'),
                'date_fin' => $dateFin->format('Y-m-d'),
            ],
        ]);
    }

    public function chartData(Request $request): JsonResponse
    {
        $periode = $request->input('periode', 'mois');

        $dateDebut = match ($periode) {
            'semaine' => now()->startOfWeek(),
            'trimestre' => now()->startOfQuarter(),
            'annee' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $dateFin = now();

        // Évolution des commandes par jour
        $evolutionCommandes = Commande::whereBetween('date_commande', [$dateDebut, $dateFin])
            ->whereNull('deleted_at')
            ->selectRaw('DATE(date_commande) as date, COUNT(*) as nombre, SUM(montant_ttc) as revenus')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'commandes' => (int) $item->nombre,
                    'revenus' => (float) $item->revenus,
                ];
            });

        // Répartition par statut
        $repartitionStatuts = Commande::whereBetween('date_commande', [$dateDebut, $dateFin])
            ->whereNull('deleted_at')
            ->selectRaw('statut, COUNT(*) as nombre')
            ->groupBy('statut')
            ->get()
            ->map(function ($item) {
                return [
                    'statut' => $item->statut,
                    'nombre' => (int) $item->nombre,
                ];
            });

        return response()->json([
            'evolution_commandes' => $evolutionCommandes,
            'repartition_statuts' => $repartitionStatuts,
        ]);
    }
}