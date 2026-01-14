<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Devis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'date_emission', 'validite', 'date_validite',
        'remise', 'acompte', 'delai_livraison', 'conditions_paiement',
        'sous_total', 'montant_remise', 'total_ht', 'total_ttc',
        'montant_acompte', 'notes', 'statut'
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_validite' => 'date',
        'remise' => 'decimal:2',
        'acompte' => 'decimal:2',
        'sous_total' => 'decimal:2',
        'montant_remise' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'montant_acompte' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($devis) {
            if (empty($devis->date_validite) && $devis->date_emission && $devis->validite) {
                $devis->date_validite = Carbon::parse($devis->date_emission)->addDays($devis->validite);
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneDevis::class)->orderBy('ordre');
    }

    public function commande()
    {
        return $this->hasOne(Commande::class);
    }

    public function calculerTotaux()
    {
        $this->sous_total = $this->lignes->sum('sous_total');
        $this->montant_remise = $this->sous_total * ($this->remise / 100);
        $this->total_ht = $this->sous_total - $this->montant_remise;
        $this->total_ttc = $this->total_ht; // Pas de TVA
        $this->montant_acompte = $this->total_ttc * ($this->acompte / 100);
        $this->save();
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'En attente');
    }
}