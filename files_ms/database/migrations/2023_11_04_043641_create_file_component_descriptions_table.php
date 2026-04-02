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
        Schema::create('file_component_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_service_composition_id')->constrained('file_service_compositions');
            $table->unsignedBigInteger('language_id');
            $table->char('code',10);
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_component_descriptions');
    }
};
