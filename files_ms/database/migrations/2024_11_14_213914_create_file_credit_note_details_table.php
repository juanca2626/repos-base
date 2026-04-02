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
        Schema::create('file_credit_note_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_credit_note_id')->constrained('file_credit_notes');
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('amount', 8, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_credit_note_details');
    }
};
