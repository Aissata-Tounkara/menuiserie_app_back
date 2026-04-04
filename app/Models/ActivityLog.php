<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    public $timestamps = false;
    
    protected $fillable = [
        'user_id', 'user_email', 'action', 'module', 'model_type',
        'model_id', 'description', 'changes', 'ip_address',
        'user_agent', 'device_type', 'device_name', 'session_id'
    ];
    
    protected $casts = [
        'changes' => 'array',
        'logged_at' => 'datetime'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // Scope pour filtrer par module
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }
    
    // Scope pour filtrer par utilisateur
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId)
                    ->orWhere('user_email', $userId);
    }
}
