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
    Schema::table('clients', function (Blueprint $table) {
        $table->dropColumn(['email','adresse','code_postal']);
    });
}

public function down()
{
    Schema::table('clients', function (Blueprint $table) {
        $table->string('email')->nullable();
        $table->text('adresse')->nullable();
        $table->string('code_postal')->nullable();
    });
}
};
