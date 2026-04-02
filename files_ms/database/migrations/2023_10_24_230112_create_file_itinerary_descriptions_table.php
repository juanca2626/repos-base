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
        Schema::create('file_itinerary_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_itinerary_id')->constrained('file_itineraries');
            $table->unsignedBigInteger('language_id');
            $table->char('code',10);
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_itinerary_descriptions');
    }
};
