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
        Schema::table('file_notes_external_housing', function (Blueprint $table) {
            $table->string('accommodation_lng')
                  ->nullable()
                  ->after('accommodation_lat')
                  ->comment('Longitud geográfica del alojamiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_notes_external_housing', function (Blueprint $table) {
            $table->dropColumn('accommodation_lng');
        });
    }
};
