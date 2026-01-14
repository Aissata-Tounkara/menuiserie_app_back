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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($facture) {
            if (empty($facture->numero_facture)) {
                $last = self::whereYear('created_at', now()->year)->latest('id')->first();
                $num = $last ? intval(substr($last->numero_facture, -3)) + 1 : 1;
                $facture->numero_facture = 'F-' . str_pad($num, 3, '0', STR_PAD_LEFT) . '/' . now()->year;
            }
        });
    }

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
      public static function genererNumero(): string
    {
        $annee = Carbon::now()->format('Y'); // Ex: "2026"

        // Trouver la dernière facture de cette année
        $derniereFacture = static::whereYear('created_at', $annee)
            ->orderBy('numero_facture', 'desc')
            ->first();

        if ($derniereFacture && preg_match('/FAC-' . $annee . '-(\d+)$/', $derniereFacture->numero_facture, $matches)) {
            $increment = (int) $matches[1] + 1;
        } else {
            $increment = 1;
        }

        return 'FAC-' . $annee . '-' . str_pad($increment, 3, '0', STR_PAD_LEFT);
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
