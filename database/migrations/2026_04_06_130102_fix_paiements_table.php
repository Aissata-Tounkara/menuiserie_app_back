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
        Schema::table('paiements', function (Blueprint $table) {
            if (!Schema::hasColumn('paiements', 'facture_id')) {
                $table->foreignId('facture_id')->nullable()->constrained('factures')->onDelete('cascade');
            }
            if (!Schema::hasColumn('paiements', 'montant')) {
                $table->decimal('montant', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('paiements', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            //
        });
    }
};
