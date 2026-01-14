<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LigneDevis extends Model
{
    use HasFactory;

    protected $fillable = [
        'devis_id', 'produit', 'categorie', 'description',
        'largeur', 'hauteur', 'quantite', 'aluminium', 'vitrage',
        'prix_unitaire', 'sous_total', 'ordre'
    ];

    protected $casts = [
        'largeur' => 'decimal:2',
        'hauteur' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'sous_total' => 'decimal:2',
    ];

    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }
}