<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['nom', 'unite', 'quantite', 'seuil_alerte', 'prix_unitaire'];

    public function mouvements() { return $this->hasMany(MouvementStock::class); }
}
