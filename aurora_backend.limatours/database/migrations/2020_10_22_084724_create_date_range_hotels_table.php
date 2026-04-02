<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDateRangeHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('date_range_hotels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date_from');
            $table->date('date_to');
            $table->double('price_adult',8,2)->default(0);
            $table->double('price_child',8,2)->default(0);
            $table->double('price_infant',8,2)->default(0);
            $table->double('price_extra',8,2)->default(0);
            $table->tinyInteger('discount_for_national')->default(0);
            $table->unsignedBigInteger('rate_plan_id');
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('rate_plan_room_id');
            $table->unsignedBigInteger('meal_id');
            $table->unsignedBigInteger('policy_id');
            $table->unsignedBigInteger('old_id_date_range')->nullable();
            $table->integer('group')->default(0);
            $table->boolean('updated')->default(1);
            $table->timestamps();

            $table->foreign('rate_plan_id')->references('id')->on('rates_plans');
            $table->foreign('hotel_id')->references('id')->on('hotels');
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->foreign('rate_plan_room_id')->references('id')->on('rates_plans_rooms');
            $table->foreign('meal_id')->references('id')->on('meals');
            $table->foreign('policy_id')->references('id')->on('policies_rates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('date_range_hotels');
    }
}
