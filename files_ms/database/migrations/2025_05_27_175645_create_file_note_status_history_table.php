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
        Schema::create('file_note_status_history', function (Blueprint $table) {
            $table->id();

            $table->enum('status', [
                'pending', //pendiente
                'received', // recibido
                'refused', // rechazado
                'approved' // aprobado
            ])->default('pending');

            $table->date('date');

            $table->text('comment')->nullable();

            $table->unsignedBigInteger('file_note_mascara_id')->nullable();
            $table->unsignedBigInteger('file_note_id')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->string('user_by_name', 100);

            $table->timestamps();
            $table->softDeletes();

            // indices
            $table->foreign('file_note_id')->references('id')->on('file_notes');
            $table->foreign('file_note_mascara_id')->references('id')->on('file_notes_mascara');

            $table->index('status');
            $table->index('date');
            $table->index(['file_note_id']);
            $table->index(['file_note_mascara_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_note_status_history');
    }
};
