<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainCancellationParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_cancellation_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('min_day');
            $table->integer('max_day');
            $table->boolean('tax');
            $table->boolean('service');
            $table->float('amount')->nullable();
            $table->unsignedBigInteger('penalty_id');
            $table->foreign('penalty_id')->references('id')->on('penalties');
            $table->unsignedBigInteger('train_cancellation_id');
            $table->foreign('train_cancellation_id')->references('id')->on('train_cancellation_policies');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('train_cancellation_parameters');
    }
}
