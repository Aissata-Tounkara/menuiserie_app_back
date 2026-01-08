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
    {
        Schema::create('devis_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_id')->constrained()->onDelete('cascade');
            $table->string('type_produit'); // porte, fenetre, vitrine, autre
            $table->string('description')->nullable();
            $table->decimal('largeur', 8, 2)->nullable(); // en mètres
            $table->decimal('hauteur', 8, 2)->nullable(); // en mètres
            $table->decimal('longueur', 8, 2)->nullable(); // pour ml
            $table->enum('unite', ['m²', 'ml', 'unite']);
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis_items');
    }
};
