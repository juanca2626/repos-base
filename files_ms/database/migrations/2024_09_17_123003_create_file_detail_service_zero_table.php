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
        Schema::create('file_detail_service_zero', function (Blueprint $table) {
            $table->id();
            $table->integer('days_before_cancellation')->nullable();
            $table->decimal('penalty_amount',10,2)->nullable();
             // Cantidad mínima y máxima de pasajeros
            $table->integer('min_passengers')->nullable(); 
            $table->integer('max_passengers')->nullable(); 
            // Edad mínima y máxima de pasajeros
            $table->integer('min_age')->nullable(); 
            $table->integer('max_age')->nullable();
            // Definición de edades para niños
            $table->integer('child_age_min')->nullable(); 
            $table->integer('child_age_max')->nullable();
            // Definición de edades para infantes
            $table->integer('infant_age_min')->nullable();
            $table->integer('infant_age_max')->nullable();
            $table->unsignedBigInteger('file_service_zero_id');
            $table->foreign('file_service_zero_id')->references('id')->on('file_service_zero');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_detail_service_zero');
    }
};
