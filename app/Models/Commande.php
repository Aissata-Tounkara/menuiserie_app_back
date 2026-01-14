<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commande extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_commande', 'client_id', 'devis_id',
        'date_commande', 'date_livraison', 'statut',
        'montant_ht', 'montant_ttc', 'notes'
    ];

    protected $casts = [
        'date_commande' => 'date',
        'date_livraison' => 'date',
        'montant_ht' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($commande) {
            if (empty($commande->numero_commande)) {
                $commande->numero_commande = 'CMD-' . now()->timestamp;
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

    public function articles()
    {
        return $this->hasMany(ArticleCommande::class);
    }

    public function facture()
    {
        return $this->hasOne(Facture::class);
    }

    // Scopes pour le dashboard
public function scopeByPeriod($query, $start, $end)
{
    return $query->whereBetween('date_commande', [$start, $end]);
}

public function scopeEnAttente($query)
{
    return $query->where('statut', 'En attente');
}

public function scopeEnProduction($query)
{
    return $query->where('statut', 'En production');
}

public function scopeLivrees($query)
{
    return $query->where('statut', 'Livrée');
}

public function scopeRecent($query, $limit = 10)
{
    return $query->with('client')->latest('date_commande')->limit($limit);
}
}