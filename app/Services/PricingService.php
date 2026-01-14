<?php

namespace App\Services;

class PricingService
{
    /**
     * Prix au mètre carré de l'aluminium (en F CFA)
     */
    public const PRIX_ALU_M2 = 45000;

    /**
     * Taux de majoration pour les formats personnalisés (15%)
     */
    public const TAUX_MAJORATION = 0.15;

    /**
     * Liste des formats standards avec leurs dimensions exactes et prix fixes.
     * 
     * ATTENTION : les noms de produits DOIVENT correspondre exactement à ceux
     * utilisés dans le frontend et dans les requêtes API.
     * 
     * Format : 'Nom du produit' => ['largeur' => X.XX, 'hauteur' => Y.YY, 'prix' => ZZZZ]
     */
    public const FORMATS_STANDARD = [
        // PORTES
        'Porte-2Battan' => ['largeur' => 1.20, 'hauteur' => 2.10, 'prix' => 147500],
        'Porte-1Battan' => ['largeur' => 0.80, 'hauteur' => 2.10, 'prix' => 97500],
        'Porte-Toilette' => ['largeur' => 0.70, 'hauteur' => 2.10, 'prix' => 87500],

        // FENÊTRES
        'Fenêtre Coulisant' => ['largeur' => 1.20, 'hauteur' => 1.10, 'prix' => 97500],
        'Fenêtre toilette' => ['largeur' => 0.60, 'hauteur' => 0.60, 'prix' => 37500],
    ];

    /**
     * Calcule le prix unitaire d'un produit en fonction du type et des dimensions.
     *
     * @param string $produit Le nom du produit (ex: 'Porte-2Battan')
     * @param float|null $largeur Largeur en mètres
     * @param float|null $hauteur Hauteur en mètres
     * @return float Prix unitaire en F CFA (arrondi à l'entier)
     */
    public static function calculerPrixUnitaire(
        string $produit,
        ?float $largeur,
        ?float $hauteur
    ): float {
        // Si les dimensions ne sont pas fournies, on ne peut pas calculer
        if ($largeur === null || $hauteur === null) {
            return 0.0;
        }

        // Vérifier si le produit existe dans les formats standards
        if (isset(self::FORMATS_STANDARD[$produit])) {
            $format = self::FORMATS_STANDARD[$produit];
            $precision = 0.01; // Tolérance de 1 cm (0.01 m)

            // Comparer les dimensions avec une tolérance
            if (
                abs($format['largeur'] - $largeur) < $precision &&
                abs($format['hauteur'] - $hauteur) < $precision
            ) {
                // Format standard → retourner le prix fixe
                return (float) $format['prix'];
            }
        }

        // Format personnalisé → calcul par surface + majoration
        $surface = $largeur * $hauteur;
        $prixBase = $surface * self::PRIX_ALU_M2;
        $prixFinal = $prixBase * (1 + self::TAUX_MAJORATION);

        // Arrondir à l'entier le plus proche (pas de centimes)
        return round($prixFinal);
    }

    /**
     * Vérifie si un produit avec des dimensions données correspond à un format standard.
     *
     * @param string $produit
     * @param float|null $largeur
     * @param float|null $hauteur
     * @return bool
     */
    public static function estFormatStandard(
        string $produit,
        ?float $largeur,
        ?float $hauteur
    ): bool {
        if ($largeur === null || $hauteur === null) {
            return false;
        }

        if (!isset(self::FORMATS_STANDARD[$produit])) {
            return false;
        }

        $format = self::FORMATS_STANDARD[$produit];
        $precision = 0.01;

        return (
            abs($format['largeur'] - $largeur) < $precision &&
            abs($format['hauteur'] - $hauteur) < $precision
        );
    }
}