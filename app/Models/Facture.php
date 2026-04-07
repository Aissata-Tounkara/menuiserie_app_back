<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

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
    // Relation avec les paiements
    // Dans Facture.php, assurez-vous que la relation est correcte :
public function paiements()
{
    return $this->hasMany(Paiement::class)
                ->orderBy('date_paiement', 'desc')
                ->withTrashed(); // Important si vous utilisez softDeletes
}

   // Total payé à partir des paiements
    public function getTotalPayeAttribute(): float
    {
        $paymentAmountColumn = $this->getPaymentAmountColumn();

        if ($this->relationLoaded('paiements')) {
            return (float) $this->paiements->sum(function ($paiement) use ($paymentAmountColumn) {
                return (float) ($paiement->{$paymentAmountColumn} ?? 0);
            });
        }

        return (float) $this->paiements()->sum($paymentAmountColumn);
    }

// Reste à payer
    public function getResteAPayerAttribute(): float
    {
        return max(0, $this->montant_ttc - $this->total_paye);
    }

// Statut réel basé sur les paiements
public function getStatutCalculeAttribute(): string
{
    $reste = $this->reste_a_payer;
    
    if ($reste <= 0) {
        return 'Payée';
    }
    
    if ($this->date_echeance && \Carbon\Carbon::parse($this->date_echeance)->isPast()) {
        return 'En retard';
    }
    
    if ($this->total_paye > 0) {
        return 'Partiellement payée';
    }
    
    return 'Non payée';
}

// Met à jour automatiquement le statut
    public function refreshStatut(): void
    {
        $this->update(['statut' => $this->statut_calcule]);
    }

    protected function getPaymentAmountColumn(): string
    {
        return Schema::hasColumn('paiements', 'montant') ? 'montant' : 'montant_paye';
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
        if (empty($facture->statut)) {
            $facture->statut = 'Non payée';
        }
    });

    // Après chaque sauvegarde, tu peux refresh le statut si tu veux
    static::saved(function ($facture) {
        // Optionnel : $facture->refreshStatut();
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
