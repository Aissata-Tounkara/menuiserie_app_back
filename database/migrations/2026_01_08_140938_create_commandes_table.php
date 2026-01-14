<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_commande')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('devis_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date_commande');
            $table->date('date_livraison')->nullable();
            $table->enum('statut', ['En attente', 'En production', 'Prête', 'Livrée', 'Annulée'])->default('En attente');
            $table->decimal('montant_ht', 12, 2);
            $table->decimal('montant_ttc', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};