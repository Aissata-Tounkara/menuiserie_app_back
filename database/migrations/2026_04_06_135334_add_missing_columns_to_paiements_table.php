<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_missing_columns_to_paiements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            // Ajoute date_paiement si elle n'existe pas
            if (!Schema::hasColumn('paiements', 'date_paiement')) {
                $table->date('date_paiement')->after('montant');
            }
            
            // Ajoute les autres colonnes manquantes au cas où
            if (!Schema::hasColumn('paiements', 'mode_paiement')) {
                $table->string('mode_paiement')->after('date_paiement');
            }
            if (!Schema::hasColumn('paiements', 'reference')) {
                $table->string('reference')->nullable()->after('mode_paiement');
            }
            if (!Schema::hasColumn('paiements', 'notes')) {
                $table->text('notes')->nullable()->after('reference');
            }
            
            // Recrée l'index si nécessaire
            if (!Schema::hasIndex('paiements', 'paiements_facture_id_date_paiement_index')) {
                $table->index(['facture_id', 'date_paiement']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropIndex(['facture_id', 'date_paiement']);
            $table->dropColumn(['date_paiement', 'mode_paiement', 'reference', 'notes']);
        });
    }
};