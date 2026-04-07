<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paiement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facture_id',
        'montant',
        'date_paiement',
        'mode_paiement',
        'reference',
        'notes'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'date',
    ];

    // Relation inverse
    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    // Formatage utile pour l'API
    public function toArray($request = null)
    {
        return [
            'id' => $this->id,
            'montant' => (float) $this->montant,
            'date_paiement' => $this->date_paiement?->format('d/m/Y'),
            'mode_paiement' => $this->mode_paiement,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}