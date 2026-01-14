<?php

namespace App\Http\Controllers;

use App\Models\MouvementStock;
use App\Models\Article;
use App\Http\Requests\MouvementStockRequest;
use App\Http\Resources\MouvementStockResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MouvementStockController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = MouvementStock::with(['article', 'user']);

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

        return MouvementStockResource::collection($mouvements);
    }

    public function store(MouvementStockRequest $request): JsonResponse
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

        $mouvement = MouvementStock::where('article_id', $article->id)
            ->latest('date_mouvement')
            ->first();

        return response()->json([
            'message' => 'Mouvement de stock enregistré avec succès',
            'data' => new MouvementStockResource($mouvement->load(['article', 'user']))
        ], 201);
    }

    public function show(MouvementStock $mouvement): JsonResponse
    {
        return response()->json([
            'data' => new MouvementStockResource($mouvement->load(['article', 'user']))
        ]);
    }

    public function destroy(MouvementStock $mouvement): JsonResponse
    {
        // Annuler le mouvement en faisant l'opération inverse
        $article = $mouvement->article;
        
        if ($mouvement->type === 'entree') {
            $article->quantite -= $mouvement->quantite;
        } else {
            $article->quantite += $mouvement->quantite;
        }
        
        $article->save();
        $mouvement->delete();

        return response()->json([
            'message' => 'Mouvement annulé avec succès'
        ]);
    }

    public function historique(Article $article): AnonymousResourceCollection
    {
        $mouvements = $article->mouvements()
            ->with('user')
            ->orderBy('date_mouvement', 'desc')
            ->paginate(20);

        return MouvementStockResource::collection($mouvements);
    }

    public function stats(Request $request): JsonResponse
    {
        $query = MouvementStock::query();

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