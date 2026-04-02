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
        Schema::table('file_itinerary_flights', function (Blueprint $table) {                  
            $table->string('airline_name')->nullable()->change();
            $table->char('airline_code', 2)->nullable()->change();
            $table->char('airline_number', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
