<?php

// app/Http/Controllers/Admin/UserManagementController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ActivityLog;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        return UserResource::collection(
            $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20))
        );
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'employee'])],
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Hashé automatiquement par le mutator
            'role' => $validated['role'],
        ]);
        
        // Sync avec Spatie Permission
        $user->syncRoles([$validated['role']]);
        
        // Logger la création
        ActivityLog::create([
            'user_id' => auth('sanctum')->id(),
            'action' => 'create',
            'module' => 'users',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Création de l'utilisateur {$user->email}",
            'ip_address' => $request->ip(),
        ]);
        
        return response()->json(new UserResource($user), 201);
    }
    
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => ['sometimes', Rule::in(['admin', 'employee'])],
        ]);
        
        // Empêcher un employé de se promouvoir lui-même
        if (isset($validated['role']) && $user->id === auth('sanctum')->id() && $validated['role'] !== 'admin') {
            return response()->json(['message' => 'Vous ne pouvez pas modifier votre propre rôle'], 403);
        }
        
        $oldData = $user->only(['name', 'email', 'role']);
        
        $user->update($validated);
        
        // Sync rôle si modifié
        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }
        
        // Logger
        ActivityLog::create([
            'user_id' => auth('sanctum')->id(),
            'action' => 'update',
            'module' => 'users',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Modification de l'utilisateur {$user->email}",
            'changes' => array_diff_assoc($validated, $oldData),
            'ip_address' => $request->ip(),
        ]);
        
        return response()->json(new UserResource($user));
    }
    
    public function destroy(User $user)
    {
        // Empêcher la suppression de soi-même ou du dernier admin
        if ($user->id === auth('sanctum')->id()) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte'], 403);
        }
        
        if ($user->hasRoleAdmin()) {
            $adminCount = User::admins()->count();
            if ($adminCount <= 1) {
                return response()->json(['message' => 'Impossible de supprimer le dernier administrateur'], 403);
            }
        }
        
        $email = $user->email;
        $user->delete();
        
        ActivityLog::create([
            'user_id' => auth('sanctum')->id(),
            'action' => 'delete',
            'module' => 'users',
            'description' => "Suppression de l'utilisateur {$email}",
            'ip_address' => request()->ip(),
        ]);
        
        return response()->json(['message' => 'Utilisateur supprimé']);
    }
    
    // Endpoint dédié pour changer uniquement le rôle
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'employee'])]
        ]);
        
        // Même protection que dans update()
        if ($user->id === auth('sanctum')->id() && $validated['role'] !== 'admin') {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }
        
        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);
        $user->syncRoles([$validated['role']]);
        
        ActivityLog::create([
            'user_id' => auth('sanctum')->id(),
            'action' => 'update',
            'module' => 'users',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Changement de rôle: {$oldRole} → {$validated['role']} pour {$user->email}",
            'ip_address' => $request->ip(),
        ]);
        
        return response()->json(new UserResource($user));
    }
}