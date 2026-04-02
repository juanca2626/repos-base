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
        Schema::create('file_notes_general', function (Blueprint $table) {
            $table->id();
            $table->date('date_event')->nullable();
            $table->string('type_event')->nullable();
            $table->longText('description_event')->nullable();
            $table->longText('description_client')->nullable();
            $table->longText('description_note_general')->nullable();
            $table->string('image_logo')->nullable();
            $table->string('classification_code')->nullable();
            $table->string('classification_name')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
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
        Schema::dropIfExists('file_notes_general');
    }
};
