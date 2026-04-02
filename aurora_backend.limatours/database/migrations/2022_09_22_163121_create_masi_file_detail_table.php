<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasiFileDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masi_file_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('file');
            $table->smallInteger('area');
            $table->smallInteger('product');
            $table->string('country');
            $table->string('client');
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
        Schema::dropIfExists('masi_file_detail');
    }
}
