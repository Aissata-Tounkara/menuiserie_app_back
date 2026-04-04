<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 

    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_email')->nullable(); // Backup si user supprimé
            $table->string('action'); // create, update, delete, login, logout
            $table->string('module'); // clients, devis, produits, stock, users
            $table->string('model_type')->nullable(); // App\Models\Client
            $table->unsignedBigInteger('model_id')->nullable(); // ID de l'élément modifié
            $table->text('description')->nullable(); // Détails de l'action
            $table->json('changes')->nullable(); // Anciennes/nouvelles valeurs
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable(); // Mobile, Desktop, Tablet
            $table->string('device_name')->nullable(); // "Samsung A21", "Windows PC"
            $table->string('session_id')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            
            $table->index(['user_id', 'logged_at']);
            $table->index(['module', 'action']);
            $table->index(['session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }

};
