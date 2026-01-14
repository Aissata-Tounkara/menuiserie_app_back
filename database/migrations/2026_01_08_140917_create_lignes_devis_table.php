<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_id')->constrained()->onDelete('cascade');
            $table->string('produit');
            $table->string('categorie')->nullable();
            $table->text('description')->nullable();
            $table->decimal('largeur', 8, 2)->nullable();
            $table->decimal('hauteur', 8, 2)->nullable();
            $table->integer('quantite')->default(1);
            $table->string('aluminium')->nullable(); // ✅ orthographe corrigée
            $table->string('vitrage')->nullable();
            $table->decimal('prix_unitaire', 12, 2);
            $table->decimal('sous_total', 12, 2);
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_devis');
    }
};