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
        Schema::create('file_notes_external_housing_passengers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('passengers_id');
            $table->unsignedBigInteger('file_notes_external_housing_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('file_notes_external_housing_id','fn_ext_housing_pass_fk')
                ->references('id')
                ->on('file_notes_external_housing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_notes_external_housing_passengers');
    }
};
