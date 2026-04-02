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
        Schema::create('file_itinerary_accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_itinerary_id')->constrained('file_itineraries');
            $table->foreignId('file_passenger_id')->constrained('file_passengers'); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_itinerary_accommodations');
    }
};
