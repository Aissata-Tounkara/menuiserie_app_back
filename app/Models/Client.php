<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB; // <--- AJOUTE CECI

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'email',
        'adresse',
        'ville',
        'code_postal',
        'type_client',
        'date_inscription',
        'nombre_commandes',
        'total_achats',
        'derniere_commande',
        'statut',
    ];

    protected $casts = [
        'date_inscription' => 'date',
        'derniere_commande' => 'date',
        'total_achats' => 'decimal:2',
    ];

    protected $appends = ['nom_complet'];

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

   // Dans Client.php
    public function commandes()
    {
        return $this->hasMany(\App\Models\Commande::class);
    }

    public function updateStatut(): void
    {
        if ($this->total_achats >= 1000000) {
            $this->statut = 'VIP';
             // Vérifie que derniere_commande n'est pas null ET qu'elle date de plus de 6 mois
        } elseif ($this->derniere_commande && $this->derniere_commande->diffInMonths(now()) > 6) {
            $this->statut = 'Inactif';
        } else {
            $this->statut = 'Actif';
        }
        $this->save();
    }

    public function scopeVip($query)
    {
        return $query->where('statut', 'VIP');
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 'Actif');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nom', 'like', "%{$search}%")
              ->orWhere('prenom', 'like', "%{$search}%")
              ->orWhere('telephone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('ville', 'like', "%{$search}%");
        });
    }

    // Ajoute cette méthode dans la classe Client
   // Client.php
public function refreshStats(): void
{
    $stats = DB::table('commandes')
        ->where('client_id', $this->id)
        ->selectRaw('COUNT(*) as nb, SUM(montant_ttc) as total, MAX(date_commande) as derniere')
        ->first();

    $this->update([
        'nombre_commandes' => $stats->nb ?? 0,
        'total_achats' => $stats->total ?? 0,
        'derniere_commande' => $stats->derniere ?? null,
    ]);
    
    $this->updateStatut(); // Ceci appelle save() à l'intérieur
}
}