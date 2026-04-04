<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeviceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $sessions = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id as session_id',
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.role',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity'
            )
            ->orderBy('last_activity', 'desc')
            ->get();
            
        $sessions = $sessions->map(function ($session) use ($request) {
            
            $detector = new DeviceDetector((object)['userAgent' => $session->user_agent]);
            
            return [
                'session_id' => $session->session_id,
                'user' => [
                    'id' => $session->user_id,
                    'name' => $session->name,
                    'email' => $session->email,
                    'role' => $session->role,
                ],
                'device_name' => $detector->getDeviceName(),
                'device_type' => $detector->getDeviceType(),
                'ip_address' => $session->ip_address,
                'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
                'is_current' => $session->session_id === $request->session()->getId(),
            ];
        });
        
        return response()->json($sessions);
    }
    
    public function destroy(Request $request, string $sessionId)
    {
        // Empêcher de se déconnecter soi-même via cette méthode
        if ($sessionId === $request->session()->getId()) {
            return response()->json([
                'message' => 'Utilisez le bouton déconnexion pour quitter votre session'
            ], 400);
        }
        
        // Supprimer la session de la base
        $deleted = DB::table('sessions')->where('id', $sessionId)->delete();
        
        // Optionnel : invalider le token Sanctum si utilisé
        // \Laravel\Sanctum\PersonalAccessToken::where('tokenable_type', 'App\Models\User')
        //     ->where('id', $sessionId) // Adaptation nécessaire selon votre implémentation
        //     ->delete();
        
        if ($deleted) {
            // Logger l'action
            ActivityLog::create([
                'user_id' => auth('sanctum')->id(),
                'action' => 'force_logout',
                'module' => 'sessions',
                'description' => "Déconnexion forcée de la session {$sessionId}",
                'ip_address' => $request->ip(),
            ]);
            
            return response()->json(['message' => 'Session fermée avec succès']);
        }
        
        return response()->json(['message' => 'Session non trouvée'], 404);
    }
}