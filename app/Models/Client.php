<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['nom', 'telephone', 'adresse', 'type'];

    public function devis() { return $this->hasMany(Devis::class); }
    public function projets() { return $this->hasManyThrough(Projet::class, Devis::class); }
}