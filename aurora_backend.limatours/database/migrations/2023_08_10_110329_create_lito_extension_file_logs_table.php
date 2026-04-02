<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLitoExtensionFileLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lito_extension_file_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lito_extension_file_id');
            $table->foreign('lito_extension_file_id')->references('id')->on('lito_extension_files');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('action');
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
        Schema::dropIfExists('lito_extension_file_logs');
    }
}
