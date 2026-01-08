<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projet extends Model
{
    protected $fillable = ['devis_id', 'nom', 'statut', 'date_debut', 'date_fin', 'note'];

    public function devis() { return $this->belongsTo(Devis::class); }
}