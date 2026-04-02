<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_service_accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_service_unit_id')->constrained('file_service_units');
            $table->foreignId('file_passenger_id')->constrained('file_passengers');
            $table->char('room_key', 3)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_service_accommodations');
    }
};
