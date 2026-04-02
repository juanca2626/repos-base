<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainClientRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_client_rate_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('period');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('train_rate_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('train_rate_id')->references('id')->on('train_rates');
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
        Schema::dropIfExists('train_client_rate_plans');
    }
}
