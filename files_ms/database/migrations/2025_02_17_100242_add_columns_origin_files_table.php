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
        Schema::table('files', function (Blueprint $table) {
            $table->string('origin')->default('aurora')->comment('el origin de la informacion de a2 o stela');
            $table->boolean('stela_processing')->nullable()->comment('0 en proceso, 1 procesado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('origin');
            $table->dropColumn('stela_processing');
        });
    }
};
