<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $fillable = ['devis_id', 'montant_total', 'montant_paye', 'statut_paiement', 'fichier_pdf'];

    public function devis() { return $this->belongsTo(Devis::class); }
}
