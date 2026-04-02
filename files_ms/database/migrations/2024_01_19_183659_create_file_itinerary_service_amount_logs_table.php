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
        Schema::create('file_itinerary_service_amount_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_itinerary_id')->constrained('file_itineraries');    
            $table->unsignedBigInteger('file_service_amount_log_id')->nullable();
            $table->foreign('file_service_amount_log_id', 'fisal_file_service_amount_log_id_foreign')->references('id')->on('file_service_amount_logs');            
            $table->decimal('value', 8, 2)->default(0);
            $table->decimal('markup', 8, 2)->default(0);
            $table->foreignId('file_amount_reason_id')->nullable()->constrained('file_amount_reasons');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_itinerary_service_amount_logs');
    }
};
