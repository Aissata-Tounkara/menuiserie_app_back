<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArticleFacture extends Model
{
    use HasFactory;

    protected $table = 'articles_facture';

    protected $fillable = [
        'facture_id', 'designation', 'quantite', 'prix_unitaire', 'total'
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }
}