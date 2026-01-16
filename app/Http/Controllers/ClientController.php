<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Resources\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\UpdateClientRequest;

class ClientController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Client::query();

        // Recherche
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filtre par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre par type
        if ($request->has('type_client')) {
            $query->where('type_client', $request->type_client);
        }

        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $clients = $query->paginate($perPage);

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return response()->json([
            'message' => 'Client créé avec succès',
            'data' => new ClientResource($client)
        ], 201);
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json([
            'data' => new ClientResource($client)
        ]);
    }

   

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());
        
        return response()->json([
            'message' => 'Client mis à jour avec succès',
            'data' => new ClientResource($client)
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json([
            'message' => 'Client supprimé avec succès'
        ]);
    }

   // Remplace par :
public function stats(): JsonResponse
{
    return response()->json([
        'total_clients' => Client::count(),
        'clients_vip' => Client::where('statut', 'VIP')->count(),
        'clients_actifs' => Client::where('statut', 'Actif')->count(),
        'total_commandes' => Client::sum('nombre_commandes'),
        'total_achats' => Client::sum('total_achats'),
    ]);
}

    public function updateStatut(Request $request, Client $client): JsonResponse
    {
        $request->validate([
            'statut' => 'required|in:Actif,Inactif,VIP'
        ]);

        $client->update(['statut' => $request->statut]);

        return response()->json([
            'message' => 'Statut mis à jour',
            'data' => new ClientResource($client)
        ]);
    }
}