<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone');
            $table->string('email')->nullable();
            $table->text('adresse');
            $table->string('ville');
            $table->string('code_postal')->nullable();
            $table->enum('type_client', ['Particulier', 'Professionnel'])->default('Particulier');
            $table->date('date_inscription');
            $table->integer('nombre_commandes')->default(0);
            $table->decimal('total_achats', 15, 2)->default(0);
            $table->date('derniere_commande')->nullable();
            $table->enum('statut', ['Actif', 'Inactif', 'VIP'])->default('Actif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};