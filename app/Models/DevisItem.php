<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevisItem extends Model
{
    protected $fillable = [
        'devis_id', 'type_produit', 'description', 'largeur', 'hauteur', 'longueur',
        'unite', 'quantite', 'prix_unitaire', 'total'
    ];

    public function devis() { return $this->belongsTo(Devis::class); }
}
