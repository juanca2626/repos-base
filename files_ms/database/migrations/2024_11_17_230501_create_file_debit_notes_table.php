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
        Schema::create('file_debit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files'); 
            $table->date('date')->nullable();  
            $table->decimal('total', 8, 2);  
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_debit_notes');
    }
};
