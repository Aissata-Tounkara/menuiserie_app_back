<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter 'Partiellement payée' à l'enum statut
        Schema::table('factures', function (Blueprint $table) {
            $table->enum('statut', ['Non payée', 'Payée', 'Partiellement payée', 'En attente', 'En retard'])
                  ->default('Non payée')
                  ->change();
        });
    }

    public function down(): void
    {
        // Revert à l'ancien enum sans 'Partiellement payée'
        Schema::table('factures', function (Blueprint $table) {
            $table->enum('statut', ['Non payée', 'Payée', 'En attente', 'En retard'])
                  ->default('Non payée')
                  ->change();
        });
    }
};
