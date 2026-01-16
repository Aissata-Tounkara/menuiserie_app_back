<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Depense extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Les attributs assignables en masse.
     */
    protected $fillable = [
        'description',
        'montant',
        'date',
        'categorie',
        
    ];

    /**
     * Les attributs à caster automatiquement.
     */
    protected $casts = [
        'montant' => 'decimal:2',
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope pour filtrer les dépenses par mois.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $month (1-12)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMonth($query, $month = null)
    {
        if ($month) {
            return $query->whereMonth('date', $month);
        }
        return $query;
    }

    /**
     * Scope pour filtrer les dépenses par catégorie.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $categorie
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategorie($query, $categorie = null)
    {
        if ($categorie) {
            return $query->where('categorie', $categorie);
        }
        return $query;
    }

    /**
     * Scope pour rechercher dans la description ou la catégorie.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search = null)
    {
        if ($search) {
            return $query->where('description', 'like', "%{$search}%")
                        ->orWhere('categorie', 'like', "%{$search}%");
        }
        return $query;
    }

    /**
     * Obtenir le total des dépenses (optionnellement par mois).
     *
     * @param int|null $month
     * @return float
     */
    public static function getTotalByMonth($month = null): float
    {
        $query = self::query();
        if ($month) {
            $query->whereMonth('date', $month);
        }
        return (float) $query->sum('montant');
    }

    /**
     * Obtenir les statistiques par catégorie (optionnellement par mois).
     *
     * @param int|null $month
     * @return \Illuminate\Support\Collection
     */
    public static function getStatsByCategorie($month = null)
    {
        $query = self::select('categorie', DB::raw('SUM(montant) as total'))
            ->groupBy('categorie');

        if ($month) {
            $query->whereMonth('date', $month);
        }

        return $query->pluck('total', 'categorie')->map(fn($value) => (float) $value);
    }
}