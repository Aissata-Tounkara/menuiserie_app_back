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

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Période par défaut : mois en cours
        $periode = $request->input('periode', 'mois');

        $dateDebut = match ($periode) {
            'semaine' => now()->startOfWeek(),
            'trimestre' => now()->startOfQuarter(),
            'annee' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $dateFin = now();

        // === STATS PRINCIPALES ===

        // Commandes
        $commandesQuery = Commande::byPeriod($dateDebut, $dateFin);
        $statsCommandes = [
            'total' => $commandesQuery->count(),
            'en_attente' => $commandesQuery->clone()->enAttente()->count(),
            'en_production' => $commandesQuery->clone()->enProduction()->count(),
            'livrees' => $commandesQuery->clone()->livrees()->count(),
        ];

        // Revenus (basés sur les commandes)
        $revenusTotal = $commandesQuery->clone()->sum('montant_ttc');

        // Factures payées (si tu veux garder cette métrique)
        $facturesPayees = Facture::payees()
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->sum('montant_ttc');

        // Clients
        $clientsQuery = Client::query();
        $statsClients = [
            'total' => $clientsQuery->count(),
            'actifs' => $clientsQuery->clone()->actif()->count(),
            'vip' => $clientsQuery->clone()->vip()->count(),
            'nouveaux' => Client::whereBetween('date_inscription', [$dateDebut, $dateFin])->count(),
        ];

        // Articles (stock)
        $statsArticles = [
            'total' => Article::count(),
            'actifs' => Article::where('quantite', '>', 0)->count(), // ou juste count()
        ];

        // === DONNÉES DÉTAILLÉES ===

        // Commandes récentes
        $commandesRecentes = Commande::with('client')
            ->recent(10)
            ->get()
            ->map(function ($commande) {
                return [
                    'id' => $commande->numero_commande,
                    'client' => optional($commande->client)->nom_complet ?? 'Client supprimé',
                    'produit' => 'Voir détails', // ou liste des articles si tu veux
                    'montant' => (float) $commande->montant_ttc,
                    'statut' => $commande->statut,
                    'date' => $commande->date_commande?->format('d/m/Y'),
                ];
            });

        // Top articles consommés (sorties de stock)
        $topArticles = Article::selectRaw('articles.*, COALESCE(SUM(ms.quantite), 0) as total_sorties')
            ->leftJoin('mouvements_stock as ms', function ($join) {
                $join->on('articles.id', '=', 'ms.article_id')
                     ->where('ms.type', '=', 'sortie');
            })
            ->groupBy('articles.id')
            ->orderByDesc('total_sorties')
            ->limit(5)
            ->get()
            ->map(function ($article) {
                return [
                    'nom' => $article->nom,
                    'consommations' => (int) $article->total_sorties,
                ];
            });

        // Alertes
        $alertes = [
            'stock_faible' => Article::enAlerte()->count(),
            'stock_critique' => Article::critique()->count(),
            'devis_en_attente' => Devis::enAttente()->count(),
            'factures_en_retard' => Facture::enRetard()->count(),
            'livraisons_du_jour' => Commande::where('date_livraison', now()->format('Y-m-d'))
                ->whereIn('statut', ['Prête', 'En production'])
                ->count(),
        ];

        // Dépenses du mois
        $depensesTotal = Depense::getTotalByMonth($dateDebut->format('Y-m'));

        return response()->json([
            'stats' => [
                'commandes' => $statsCommandes,
                'revenus' => [
                    'total' => $revenusTotal,
                    'factures_payees' => $facturesPayees,
                ],
                'clients' => $statsClients,
                'articles' => $statsArticles,
                'depenses' => [
                    'total_mois' => $depensesTotal,
                ],
            ],
            'commandes_recentes' => $commandesRecentes,
            'top_produits' => $topArticles, // ce sont des articles consommés
            'alertes' => $alertes,
            'periode' => $periode,
            'date_debut' => $dateDebut->format('Y-m-d'),
            'date_fin' => $dateFin->format('Y-m-d'),
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

        // Évolution des commandes
        $evolutionCommandes = Commande::byPeriod($dateDebut, now())
            ->selectRaw('DATE(date_commande) as date, COUNT(*) as commandes, SUM(montant_ttc) as revenus')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'commandes' => (int) $item->commandes,
                    'revenus' => (float) $item->revenus,
                ];
            });

        // Top articles (pour graphique)
        $ventesParArticle = Article::selectRaw('articles.nom, COALESCE(SUM(ms.quantite), 0) as ventes')
            ->leftJoin('mouvements_stock as ms', function ($join) {
                $join->on('articles.id', '=', 'ms.article_id')
                     ->where('ms.type', '=', 'sortie');
            })
            ->groupBy('articles.id')
            ->orderByDesc('ventes')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->nom,
                    'ventes' => (int) $item->ventes,
                ];
            });

        return response()->json([
            'ventes_par_produit' => $ventesParArticle,
            'evolution_commandes' => $evolutionCommandes,
        ]);
    }
}