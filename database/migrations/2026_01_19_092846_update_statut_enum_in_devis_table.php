<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Mettre à jour les enregistrements existants vers des valeurs compatibles
        // On convertit :
        // - 'brouillon', 'envoye', 'refuse' → 'accepte' (ou 'expire' selon ton besoin, ici on choisit 'accepte')
        DB::statement("
            UPDATE devis SET statut = 'accepte'
            WHERE statut IN ('brouillon', 'envoye', 'refuse')
        ");

        // 2. Modifier la colonne en recréant l'enum avec les nouvelles valeurs
        DB::statement("
            ALTER TABLE devis
            MODIFY COLUMN statut ENUM('accepte', 'expire') NOT NULL DEFAULT 'accepte'
        ");
    }

    public function down(): void
    {
        // Restaurer l'ancien enum
        DB::statement("
            ALTER TABLE devis
            MODIFY COLUMN statut ENUM('brouillon', 'envoye', 'accepte', 'refuse', 'expire') NOT NULL DEFAULT 'brouillon'
        ");
    }
};