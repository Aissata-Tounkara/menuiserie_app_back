<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Facture extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_facture', 'commande_id', 'client_id',
        'date_emission', 'date_echeance',
        'montant_ht', 'tva', 'montant_ttc', 'montant_paye',
        'statut', 'mode_paiement', 'date_paiement', 'notes'
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'date_paiement' => 'date',
        'montant_ht' => 'decimal:2',
        'tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'montant_paye' => 'decimal:2',
    ];


    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function articles()
    {
        return $this->hasMany(ArticleFacture::class);
    }

    public function getStatutCalculeAttribute()
    {
        if ($this->montant_paye >= $this->montant_ttc) return 'Payée';
        if (Carbon::parse($this->date_echeance)->isPast()) return 'En retard';
        return 'En attente';
    }

    /**
     * Génère le prochain numéro de facture au format FAC-AAAA-NNN.
     */

protected static function boot()
{
    parent::boot();

    static::creating(function ($facture) {
        if (empty($facture->numero_facture)) {
            $facture->numero_facture = self::genererNumeroUnique();
        }
    });
}

    public static function genererNumeroUnique(): string
    {
        $annee = now()->year;
        $prefix = 'FAC-' . $annee . '-';

        // Trouver le dernier numéro de cette année
        $last = self::where('numero_facture', 'like', $prefix . '%')
                    ->orderBy('numero_facture', 'desc')
                    ->first();

        if ($last && preg_match('/' . preg_quote($prefix, '/') . '(\d+)$/', $last->numero_facture, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
    // pour le dashbord
    public function scopePayees($query)
    {
        return $query->where('statut', 'Payée');
    }

    public function scopeEnRetard($query)
    {
        return $query->where('statut', '!=', 'Payée')
                    ->where('date_echeance', '<', now()->format('Y-m-d'));
    }
}
