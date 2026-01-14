<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero_facture')->unique();
            $table->foreignId('commande_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->date('date_emission');
            $table->date('date_echeance');
            $table->decimal('montant_ht', 12, 2);
            $table->decimal('tva', 12, 2)->default(0); // ✅ mais toujours 0
            $table->decimal('montant_ttc', 12, 2);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->enum('statut', ['Non payée', 'Payée', 'En attente', 'En retard'])->default('Non payée');
            $table->string('mode_paiement')->nullable();
            $table->date('date_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};