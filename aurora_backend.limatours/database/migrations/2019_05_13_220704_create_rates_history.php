<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateRatesHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rates_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rates_plan_id');
            $table->unsignedBigInteger('policies_rate_id');
            $table->unsignedBigInteger('meal_id');
            $table->unsignedBigInteger('hotel_id');
            $table->longText('data');
            $table->timestamps();

            $table->foreign('rates_plan_id')->references('id')->on('rates_plans');
            $table->foreign('policies_rate_id')->references('id')->on('policies_rates');
            $table->foreign('meal_id')->references('id')->on('meals');
            $table->foreign('hotel_id')->references('id')->on('hotels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rates_histories');
    }
}
