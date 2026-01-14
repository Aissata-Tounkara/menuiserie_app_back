<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->date('date_emission');
            $table->integer('validite')->default(30);
            $table->date('date_validite');
            $table->decimal('remise', 8, 2)->default(0);
            $table->decimal('acompte', 8, 2)->default(0);
            $table->string('delai_livraison')->nullable();
            $table->string('conditions_paiement')->nullable();
            $table->decimal('sous_total', 12, 2)->default(0);
            $table->decimal('montant_remise', 12, 2)->default(0);
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->decimal('montant_acompte', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('statut', ['brouillon', 'envoye', 'accepte', 'refuse', 'expire'])->default('brouillon');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};