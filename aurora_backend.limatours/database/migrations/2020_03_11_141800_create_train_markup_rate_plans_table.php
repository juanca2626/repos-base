<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainMarkupRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_markup_rate_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('markup', 10, 2);
            $table->integer('period');
            $table->unsignedBigInteger('client_id')->unsigned()->index();
            $table->unsignedBigInteger('train_rate_id')->unsigned()->index();
            $table->foreign('train_rate_id')->references('id')->on('train_rates');
            $table->foreign('client_id')->references('id')->on('clients');
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
        Schema::dropIfExists('train_markup_rate_plans');
    }
}
