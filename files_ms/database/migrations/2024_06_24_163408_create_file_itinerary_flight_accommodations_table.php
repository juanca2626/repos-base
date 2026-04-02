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
        Schema::create('file_itinerary_flight_accommodations', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('file_itinerary_flight_id')->constrained('file_itinerary_flights');
            $table->foreignId('file_passenger_id')->constrained('file_passengers'); 

            $table->unsignedBigInteger('file_itinerary_flight_id')->nullable();
            $table->foreign('file_itinerary_flight_id', 'fisal_file_itinerary_flight_id_foreign_accommodations')->references('id')->on('file_itinerary_flights')->onDelete('cascade'); 

            $table->timestamps();
            $table->softDeletes();
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
