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
        Schema::create('file_notes_external_housing', function (Blueprint $table) {
            $table->id();
            $table->date('date_check_in');
            $table->date('date_check_out');
            $table->string('accommodation_name')->nullable();
            $table->string('accommodation_code_phone')->nullable();
            $table->string('accommodation_number_phone')->nullable();
            $table->string('accommodation_address')->nullable();
            $table->string('accommodation_lat')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('file_itinerary_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('file_id')->references('id')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_notes_external_housing');
    }
};
