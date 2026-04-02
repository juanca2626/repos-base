<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLitoExtensionFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lito_extension_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('file');
            $table->unsignedBigInteger('lito_extension_file_type_id');
            $table->foreign('lito_extension_file_type_id')->references('id')->on('lito_extension_file_types');
            $table->string('link')->nullable();
            $table->string('original_name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lito_extension_files');
    }
}
