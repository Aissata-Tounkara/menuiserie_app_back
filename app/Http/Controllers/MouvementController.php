<?php

namespace App\Http\Controllers;

use App\Models\Mouvement;
use App\Models\Article;
use App\Http\Requests\MouvementRequest;
use App\Http\Resources\MouvementResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MouvementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Mouvement::with(['article', 'user']);
        // Filtre par article
        if ($request->has('article_id')) {
            $query->byArticle($request->article_id);
        }

        // Filtre par type
        if ($request->has('type')) {
            if ($request->type === 'entree') {
                $query->entrees();
            } elseif ($request->type === 'sortie') {
                $query->sorties();
            }
        }

        // Filtre par période
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->byPeriod($request->date_debut, $request->date_fin);
        }

        // Tri
        $sortBy = $request->input('sort_by', 'date_mouvement');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 20);
        $mouvements = $query->paginate($perPage);

        return MouvementResource::collection($mouvements);
    }

    public function store(MouvementRequest $request): JsonResponse
    {
        $article = Article::findOrFail($request->article_id);
        
        // Vérifier si on a assez de stock pour une sortie
        if ($request->type === 'sortie' && $article->quantite < $request->quantite) {
            return response()->json([
                'message' => 'Stock insuffisant',
                'stock_disponible' => $article->quantite,
                'quantite_demandee' => $request->quantite
            ], 422);
        }

        $article->ajusterStock(
            $request->quantite,
            $request->type,
            $request->motif,
            $request->commentaire
        );

        $mouvement = Mouvement::where('article_id', $article->id)
            ->latest('date_mouvement')
            ->first();

        return response()->json([
            'message' => 'Mouvement de stock enregistré avec succès',
            'data' => new MouvementResource($mouvement->load(['article', 'user']))
        ], 201);
    }

   public function show(Mouvement $mouvement): JsonResponse
{
    // Charger la relation article
    $mouvement->load('article');

    return response()->json([
        'data' => new MouvementResource($mouvement)
    ]);
}

 public function destroy(Mouvement $mouvement): JsonResponse
{
    // Récupérer l'article associé, même s'il a été soft-supprimé (pour historique)
    $article = Article::withTrashed()->where('id', $mouvement->article_id)->first();

    if (!$article) {
        return response()->json([
            'message' => 'Article associé introuvable'
        ], 404);
    }

    // Réajuster le stock selon le type de mouvement
    if ($mouvement->type === 'entree') {
        // Annuler une entrée → on retire la quantité
        $article->quantite -= $mouvement->quantite;
    } elseif ($mouvement->type === 'sortie') {
        // Annuler une sortie → on remet la quantité en stock
        $article->quantite += $mouvement->quantite;
    } else {
        return response()->json([
            'message' => 'Type de mouvement invalide'
        ], 422);
    }

    // Mettre à jour les dates de dernière entrée/sortie si nécessaire
    if ($mouvement->type === 'entree' && $article->derniere_entree?->isSameDay($mouvement->date_mouvement)) {
        // Optionnel : recalculer la dernière entrée si besoin
        $lastEntree = $article->mouvements()
            ->entrees()
            ->where('id', '!=', $mouvement->id)
            ->max('date_mouvement');
        $article->derniere_entree = $lastEntree;
    }

    if ($mouvement->type === 'sortie' && $article->derniere_sortie?->isSameDay($mouvement->date_mouvement)) {
        $lastSortie = $article->mouvements()
            ->sorties()
            ->where('id', '!=', $mouvement->id)
            ->max('date_mouvement');
        $article->derniere_sortie = $lastSortie;
    }

    // Sauvegarder l'article
    $article->save();

    // Supprimer le mouvement
    $mouvement->delete();

    return response()->json([
        'message' => 'Mouvement annulé avec succès'
    ]);
}

    public function stats(Request $request): JsonResponse
    {
        $query = Mouvement::query();

        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->byPeriod($request->date_debut, $request->date_fin);
        }

        return response()->json([
            'total_entrees' => $query->clone()->entrees()->sum('quantite'),
            'total_sorties' => $query->clone()->sorties()->sum('quantite'),
            'nombre_entrees' => $query->clone()->entrees()->count(),
            'nombre_sorties' => $query->clone()->sorties()->count(),
        ]);
    }
}