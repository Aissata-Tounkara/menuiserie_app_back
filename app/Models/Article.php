<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'reference',
        'categorie',
        'quantite',
        'unite',
        'seuil_alerte',
        'prix_achat',
        'fournisseur',
        'emplacement',
        'derniere_entree',
        'derniere_sortie',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'seuil_alerte' => 'integer',
        'prix_achat' => 'decimal:2',
        'derniere_entree' => 'date',
        'derniere_sortie' => 'date',
    ];

    protected $appends = ['valeur_totale', 'statut_stock'];

    public function mouvements(): HasMany
    {
        return $this->hasMany(Mouvement::class);
    }

    public function getValeurTotaleAttribute(): float
    {
        return $this->quantite * $this->prix_achat;
    }

  public function getStatutStockAttribute(): string
{
    // Stock vide → prioritaire
    if ($this->quantite == 0) {
        return 'Épuisé';
    }

    // 🔹 RÈGLES ABSOLUES (spécifiques à la menuiserie)
    // Même si le ratio semble bon, peu de pièces = risque réel
    if ($this->quantite <= 10) {
        return 'Critique';
    }
    if ($this->quantite <= 15) {
        return 'Faible';
    }
    if ($this->quantite <= 20) {
        return 'Moyen';
    }

    // 🔹 RÈGLES RELATIVES (fallback pour les gros stocks)
    if ($this->seuil_alerte == 0) {
        return 'Bon';
    }

    $ratio = $this->quantite / $this->seuil_alerte;

    if ($ratio <= 0.5) return 'Critique';
    if ($ratio <= 1) return 'Faible';
    if ($ratio <= 2) return 'Moyen';

    return 'Bon';
}

    public function isEnAlerte(): bool
    {
        return $this->quantite <= $this->seuil_alerte;
    }

    public function isCritique(): bool
    {
        return $this->quantite <= ($this->seuil_alerte * 0.5);
    }

    public function ajusterStock(int $quantite, string $type = 'entree', ?string $motif = null, ?string $commentaire = null): void
    {
        $quantiteAvant = $this->quantite;

        if ($type === 'entree') {
            $this->quantite += $quantite;
            $this->derniere_entree = now();
        } else {
            $this->quantite -= $quantite;
            $this->derniere_sortie = now();
        }

        $this->save();

        // Créer le mouvement de stock
        Mouvement::create([
            'article_id' => $this->id,
            'type' => $type,
            'quantite' => $quantite,
            'quantite_avant' => $quantiteAvant,
            'quantite_apres' => $this->quantite,
            'motif' => $motif,
            'commentaire' => $commentaire,
            'date_mouvement' => now(),
        ]);
    }

    public function scopeEnAlerte($query)
    {
        return $query->whereRaw('quantite <= seuil_alerte');
    }

    public function scopeCritique($query)
    {
        return $query->whereRaw('quantite <= (seuil_alerte * 0.5)');
    }

    public function scopeByCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nom', 'like', "%{$search}%")
              ->orWhere('reference', 'like', "%{$search}%");
        });
    }

    public static function getValeurTotaleStock()
    {
        return self::selectRaw('SUM(quantite * prix_achat) as total')->value('total') ?? 0;
    }
    // Dans App\Models\Article
public static function topConsommes($limit = 5)
{
    // Sélectionner tous les champs de la table articles
    // + calculer le total des quantités sorties pour chaque article
    return self::selectRaw(
            'articles.*, COALESCE(SUM(ms.quantite), 0) as total_sorties'
        )

        // Jointure avec la table mouvements_stock (alias ms)
        // On fait une jointure gauche pour inclure aussi les articles
        // qui n'ont encore aucun mouvement de sortie
        ->leftJoin('mouvements as ms', function ($join) {

            // Lier l'article au mouvement de stock
            $join->on('articles.id', '=', 'ms.article_id')

                 // Filtrer uniquement les mouvements de type "sortie"
                 // (ventes, factures, consommation de stock)
                 ->where('ms.type', '=', 'sortie');
        })

        // Grouper par article pour pouvoir faire le SUM
        ->groupBy('articles.id')

        // Trier par quantité totale sortie (du plus consommé au moins consommé)
        ->orderByDesc('total_sorties')

        // Limiter le nombre de résultats (par défaut : 5 articles)
        ->limit($limit)

        // Exécuter la requête et retourner la collection
        ->get();
}       
}