<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use App\Http\Requests\DepenseRequest;
use App\Http\Resources\DepenseResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Depense::withTrashed()->when($request->filled('include_deleted'), fn($q) => $q);

        $query->search($request->search)
              ->when($request->filled('month'), fn($q) => $q->byMonth($request->month))
              ->when($request->filled('categorie'), fn($q) => $q->byCategorie($request->categorie))
              ->orderBy($request->input('sort_by', 'date'), $request->input('sort_order', 'desc'));

        return DepenseResource::collection(
            $query->paginate($request->input('per_page', 15))
        );
    }

    public function store(DepenseRequest $request): JsonResponse
    {
        $depense = Depense::create($request->validated());
        return response()->json([
            'message' => 'Dépense créée avec succès',
            'data' => new DepenseResource($depense)
        ], 201);
    }

    public function show(Depense $depense): JsonResponse
    {
        return response()->json(['data' => new DepenseResource($depense)]);
    }

    public function update(DepenseRequest $request, Depense $depense): JsonResponse
    {
        $depense->update($request->validated());
        return response()->json([
            'message' => 'Dépense mise à jour',
            'data' => new DepenseResource($depense)
        ]);
    }

    public function destroy(Depense $depense): JsonResponse
    {
        $depense->delete(); // soft delete
        return response()->json(['message' => 'Dépense supprimée']);
    }

    // Restaurer une dépense (optionnel mais utile)
    public function restore(int $id): JsonResponse
    {
        $depense = Depense::withTrashed()->findOrFail($id);
        $depense->restore();
        return response()->json(['message' => 'Dépense restaurée']);
    }

    public function stats(Request $request): JsonResponse
    {
        $month = $request->input('month');
        return response()->json([
            'total' => Depense::getTotalByMonth($month),
            'count' => Depense::when($month, fn($q) => $q->byMonth($month))->count(),
            'by_categorie' => Depense::getStatsByCategorie($month),
        ]);
    }
}