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
        Schema::create('file_service_zero', function (Blueprint $table) {
            $table->id();
            $table->string('time');
            $table->string('type');
            $table->string('privacy');
            $table->longText('name');
            $table->enum('status', ['pending','canceled','active'])->default('pending');
            $table->string('skeleton')->nullable();
            $table->string('itinerary')->nullable();
            $table->string('supplier_code')->nullable();
            $table->string('supplier')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_service_zero');
    }
};
