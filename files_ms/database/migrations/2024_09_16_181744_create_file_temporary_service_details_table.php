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
 
        Schema::create('file_temporary_service_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_temporary_service_id')->constrained('file_temporary_services');
            $table->foreignId('language_id')->constrained('languages');
            $table->longText('itinerary')->nullable();
            $table->longText('skeleton')->nullable();
            $table->longText('service_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_service_details');
    }
};
