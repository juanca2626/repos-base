<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainTrainClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_train_classes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code',45);
            $table->unsignedBigInteger('train_id');
            $table->unsignedBigInteger('train_class_id');
            $table->foreign('train_id')->references('id')->on('trains');
            $table->foreign('train_class_id')->references('id')->on('train_classes');
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
        Schema::dropIfExists('train_train_classes');
    }
}
