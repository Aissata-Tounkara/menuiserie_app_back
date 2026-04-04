<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user:id,name,email');
        
        // Filtres
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }
        if ($request->filled('module')) {
            $query->byModule($request->module);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->where('logged_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('logged_at', '<=', $request->date_to . ' 23:59:59');
        }
        
        // Tri
        $sortBy = $request->get('sort_by', 'logged_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        
        return response()->json($query->paginate($perPage));
    }
    
    public function show(ActivityLog $activity)
    {
        return response()->json($activity->load('user'));
    }
    
    public function destroy(ActivityLog $activity)
    {
        $activity->delete();
        
        // Logger cette action de suppression de log
        ActivityLog::create([
            'action' => 'delete',
            'module' => 'activities',
            'description' => "Suppression d'un journal d'activité #{$activity->id}",
            'user_id' => auth('sanctum')->id(),
            'ip_address' => request()->ip(),
        ]);
        
        return response()->json(['message' => 'Journal supprimé']);
    }
}