<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelRatePlanReleasedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_rate_plan_released', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rate_plan_id');
            $table->foreign('rate_plan_id')->references('id')->on('rates_plans');
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
        Schema::dropIfExists('hotel_released');
    }
}
