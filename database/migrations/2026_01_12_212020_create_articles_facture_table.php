<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles_facture', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained()->onDelete('cascade');
            $table->string('designation');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles_facture');
    }
};
