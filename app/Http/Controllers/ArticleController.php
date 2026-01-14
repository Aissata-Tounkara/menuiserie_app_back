<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Requests\ArticleRequest;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Article::query();

        // Recherche
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filtre par catégorie
        if ($request->has('categorie') && $request->categorie !== 'Tous') {
            $query->byCategorie($request->categorie);
        }

        // Filtre alertes
        if ($request->has('en_alerte') && $request->en_alerte) {
            $query->enAlerte();
        }

        if ($request->has('critique') && $request->critique) {
            $query->critique();
        }

        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $articles = $query->paginate($perPage);

        return ArticleResource::collection($articles);
    }

    public function store(ArticleRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        if (!isset($data['derniere_entree'])) {
            $data['derniere_entree'] = now();
        }

        $article = Article::create($data);

        return response()->json([
            'message' => 'Article créé avec succès',
            'data' => new ArticleResource($article)
        ], 201);
    }

    public function show(Article $article): JsonResponse
    {
        return response()->json([
            'data' => new ArticleResource($article)
        ]);
    }

    public function update(ArticleRequest $request, Article $article): JsonResponse
    {
        $article->update($request->validated());

        return response()->json([
            'message' => 'Article mis à jour avec succès',
            'data' => new ArticleResource($article)
        ]);
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json([
            'message' => 'Article supprimé avec succès'
        ]);
    }

    public function ajusterStock(Request $request, Article $article): JsonResponse
    {
        $request->validate([
            'quantite' => 'required|integer|min:1',
            'type' => 'required|in:entree,sortie',
            'motif' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        // Vérification critique
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

        return response()->json([
            'message' => 'Stock mis à jour',
            'data' => new ArticleResource($article)
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total_articles' => Article::count(),
            'valeur_stock' => Article::getValeurTotaleStock(),
            'alertes' => Article::enAlerte()->count(),
            'critiques' => Article::critique()->count(),
            'by_categorie' => Article::selectRaw('categorie, COUNT(*) as count')
                ->groupBy('categorie')
                ->get(),
        ]);
    }

    public function alertes(): AnonymousResourceCollection
    {
        $articles = Article::enAlerte()->orderBy('quantite', 'asc')->get();
        return ArticleResource::collection($articles);
    }
}