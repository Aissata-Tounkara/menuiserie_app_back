<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->string('nom');
    $table->string('reference')->unique();
    $table->string('categorie');
    $table->integer('quantite')->default(0);
    $table->string('unite');
    $table->integer('seuil_alerte')->default(10);
    $table->decimal('prix_achat', 15, 2)->nullable();
    $table->string('fournisseur')->nullable();
    $table->string('emplacement')->nullable();
    $table->date('derniere_entree')->nullable();
    $table->date('derniere_sortie')->nullable();
    $table->softDeletes();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
