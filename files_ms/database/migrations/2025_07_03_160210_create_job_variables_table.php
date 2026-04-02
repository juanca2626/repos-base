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
        Schema::dropIfExists('job_variables');
        Schema::create('job_variables', function (Blueprint $table) {
            $table->id();
            $table->string('module')->nullable();
            $table->string('file')->nullable();
            $table->string('key');
            $table->longText('value');
            $table->timestamps();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_variables');
    }
};
