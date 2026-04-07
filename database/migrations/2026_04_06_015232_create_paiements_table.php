<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            $table->decimal('montant', 10, 2); // Montant du paiement
            $table->date('date_paiement');
            $table->string('mode_paiement'); // Espèces, Virement, Mobile Money, etc.
            $table->string('reference')->nullable(); // Référence transaction
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour les recherches fréquentes
            $table->index(['facture_id', 'date_paiement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};