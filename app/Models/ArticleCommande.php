<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArticleCommande extends Model
{
    use HasFactory;

    protected $table = 'articles_commande';

    protected $fillable = [
        'commande_id', 'produit', 'quantite', 'dimensions', 'prix'
    ];

    protected $casts = [
        'prix' => 'decimal:2',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}