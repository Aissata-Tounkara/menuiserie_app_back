<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->hasRoleAdmin()) {
            return response()->json([
                'message' => 'Accès réservé aux administrateurs'
            ], 403);
        }
        
        // Optionnel : logger l'accès admin
        // activity()->log("Accès à {$request->path()}");
        
        return $next($request);
    }
}
