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
        Schema::table('file_itineraries', function (Blueprint $table) {
            $table->boolean('add_to_statement')->default(1)->comment('me indica si este equivalencia ya esta añadido al statement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_itineraries', function (Blueprint $table) {
            $table->dropColumn('add_to_statement');
        });
    }
};
