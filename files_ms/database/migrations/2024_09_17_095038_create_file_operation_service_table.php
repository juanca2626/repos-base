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
        Schema::create('file_operation_service', function (Blueprint $table) {
            $table->id();
            $table->string('city_in');
            $table->string('city_out');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('start_validity');
            $table->date('end_validity');
            $table->string('days_operation');
            $table->string('operating_hours');
            $table->string('code_country');
            $table->string('country');
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
        Schema::dropIfExists('file_operation_service');
    }
};
