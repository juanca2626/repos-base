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
            $table->string('city', 100)->nullable()->after('file_itinerary_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_notes_external_housing', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }
};
