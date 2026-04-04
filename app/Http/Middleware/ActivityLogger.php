<?php
// app/Http/Middleware/ActivityLogger.php
namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Services\DeviceDetector;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'refresh_token',
        'remember_token',
        'authorization',
        'code',
    ];

    // Modules à logger et leurs routes associées
    protected array $trackedModules = [
        'clients' => ['clients'],
        'devis' => ['devis', 'quotes'],
        'commandes' => ['commandes', 'orders'],
        'depenses' => ['depenses', 'expenses'],
        'articles' => ['articles', 'products', 'produits'],
        'mouvement' => ['mouvement', 'mouvements', 'stock', 'inventory'],
        'users' => ['users', 'employes'],
        'factures' => ['invoices', 'factures'],
        'sessions' => ['sessions'],
    ];
    
    // Actions à ignorer (GET simples, assets, etc.)
    protected array $ignoredMethods = ['GET', 'HEAD', 'OPTIONS'];
    
    protected array $ignoredRoutes = [
        'api/login', 'api/logout', 'api/register',
        'api/password/*', 'api/me', 'api/activities',
        'sanctum/*', 'livewire/*'
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Skip logging for ignored methods/routes
        if (in_array($request->method(), $this->ignoredMethods)) {
            return $response;
        }
        
        if ($this->shouldIgnoreRoute($request->path())) {
            return $response;
        }

        try {
            $this->logActivity($request, $response);
        } catch (\Throwable $exception) {
            report($exception);
        }
        
        return $response;
    }
    
    protected function shouldIgnoreRoute(string $path): bool
    {
        foreach ($this->ignoredRoutes as $pattern) {
            if (str_starts_with($path, rtrim($pattern, '*'))) {
                return true;
            }
        }
        return false;
    }
    
    protected function logActivity(Request $request, $response): void
    {
        $user = Auth::guard('sanctum')->user();
        
        // Déterminer le module concerné
        $module = $this->detectModule($request->path());
        if (!$module) return;
        
        // Détecter l'action
        $action = $this->detectAction($request->method(), $response->getStatusCode());
        if (!$action) return;
        
        // Analyse de l'appareil
        $detector = new DeviceDetector($request);
        
        ActivityLog::create([
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'action' => $action,
            'module' => $module,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $detector->getDeviceType(),
            'device_name' => $detector->getDeviceName(),
            'session_id' => $this->resolveSessionId($request, $request->bearerToken()),
            'description' => $this->generateDescription($module, $action, $request),
            'changes' => $request->method() !== 'GET' ? $this->sanitizeChanges($request->all()) : null,
        ]);
    }

    protected function resolveSessionId(Request $request, ?string $fallback = null): ?string
    {
        if ($request->hasSession()) {
            return $request->session()->getId();
        }

        return $fallback;
    }

    protected function sanitizeChanges(array $changes): array
    {
        $sanitized = [];

        foreach ($changes as $key => $value) {
            if (in_array(strtolower((string) $key), $this->sensitiveFields, true)) {
                $sanitized[$key] = '[FILTERED]';
                continue;
            }

            $sanitized[$key] = is_array($value)
                ? $this->sanitizeChanges($value)
                : $value;
        }

        return $sanitized;
    }
    
    protected function detectModule(string $path): ?string
    {
        $path = strtolower($path);
        
        foreach ($this->trackedModules as $module => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($path, $keyword)) {
                    return $module;
                }
            }
        }
        return null;
    }
    
    protected function detectAction(string $method, int $statusCode): ?string
    {
        if ($statusCode >= 400) return null; // Ne pas logger les erreurs
        
        return match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => null
        };
    }
    
    protected function generateDescription(string $module, string $action, Request $request): string
    {
        $resource = rtrim($request->path(), '/0123456789');
        $id = preg_match('/\/(\d+)$/', $request->path(), $matches) ? $matches[1] : null;
        
        return sprintf(
            '%s %s %s',
            ucfirst($action),
            ucfirst(rtrim($module, 's')), // "client" au lieu de "clients"
            $id ? "(#{$id})" : ''
        );
    }
}
