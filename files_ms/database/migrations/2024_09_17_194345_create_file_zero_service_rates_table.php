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
        Schema::create('file_zero_service_rates', function (Blueprint $table) {
            $table->id();
            $table->string('type_passenger');
            $table->integer('passenger_range_min')->nullable(); 
            $table->integer('passenger_range_max')->nullable(); 
            $table->decimal('net_cost',10,2);
            $table->integer('service_tax')->nullable(); 
            $table->integer('general_tax')->nullable(); 
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
        Schema::dropIfExists('file_zero_service_rates');
    }
};
