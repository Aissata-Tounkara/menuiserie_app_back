<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->enum('categorie', ['Achat matériaux', 'Transport', 'Électricité', 'Maintenance', 'Autre']);
            $table->string('description');
            $table->decimal('montant', 15, 2);
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};