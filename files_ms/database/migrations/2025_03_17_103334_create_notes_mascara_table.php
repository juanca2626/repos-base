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
        Schema::create('file_notes_mascara', function (Blueprint $table) {
            $table->id();
            $table->enum('type_note',['INFORMATIVE','REQUIREMENT']);
            $table->enum('record_type',['FOR_FILE','FOR_DATE','EXTERNAL_HOUSING']);
            $table->enum('assignment_mode',['ALL_DAY','FOR_SERVICE']);
            $table->json('dates')->nullable();
            $table->longText('description');
            $table->string('file_classification_code')->nullable();
            $table->string('file_classification_name')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->char('status',1)->default('1');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_name')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('file_classification_id')->references('id')->on('file_classifications');
            $table->foreign('file_id')->references('id')->on('files');
            // $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_notes_mascara');
    }
};
