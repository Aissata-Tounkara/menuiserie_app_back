<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mouvement extends Model
{
    use HasFactory;


    protected $fillable = [
        'article_id',
        'type',
        'quantite',
        'quantite_avant',
        'quantite_apres',
        'motif',
        'commentaire',
        'reference_document',
        'user_id',
        'date_mouvement',
    ];

    protected $casts = [
        'date_mouvement' => 'datetime',
        'quantite' => 'integer',
        'quantite_avant' => 'integer',
        'quantite_apres' => 'integer',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeEntrees($query)
    {
        return $query->where('type', 'entree');
    }

    public function scopeSorties($query)
    {
        return $query->where('type', 'sortie');
    }

    public function scopeByArticle($query, $articleId)
    {
        return $query->where('article_id', $articleId);
    }

    public function scopeByPeriod($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_mouvement', [$dateDebut, $dateFin]);
    }
    //  un scope pour exclure les mouvements liés à des articles supprimés :
    public function scopeValid($query)
    {
        return $query->whereHas('article');
    }

    protected static function booted()
    {
        // Créer automatiquement un mouvement lors de la modification du stock
        static::creating(function ($mouvement) {
            if (!$mouvement->date_mouvement) {
                $mouvement->date_mouvement = now();
            }
        });
    }
}