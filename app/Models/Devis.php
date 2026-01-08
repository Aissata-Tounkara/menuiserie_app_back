<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devis extends Model
{
    protected $fillable = ['client_id', 'date', 'statut', 'total', 'fichier_pdf'];

    public function client() { return $this->belongsTo(Client::class); }
    public function items() { return $this->hasMany(DevisItem::class); }
    public function projet() { return $this->hasOne(Projet::class); }
    public function facture() { return $this->hasOne(Facture::class); }
}