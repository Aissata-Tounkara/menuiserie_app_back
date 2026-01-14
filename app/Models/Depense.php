<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Depense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categorie',
        'description',
        'montant',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'montant' => 'decimal:2',
    ];

    // Scopes pour filtres
    public function scopeByMonth($query, string $month)
    {
        return $query->whereYear('date', substr($month, 0, 4))
                     ->whereMonth('date', substr($month, 5, 2));
    }

    public function scopeByCategorie($query, string $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('categorie', 'like', "%{$search}%");
        });
    }

    // Stats
    public static function getTotalByMonth(?string $month = null): float
    {
        $query = self::query();
        if ($month) $query->byMonth($month);
        return $query->sum('montant');
    }

    public static function getStatsByCategorie(?string $month = null)
    {
        $query = self::query();
        if ($month) $query->byMonth($month);
        return $query->selectRaw('categorie, SUM(montant) as total')
                     ->groupBy('categorie')
                     ->get();
    }
}