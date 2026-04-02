<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateRatesPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rates_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 45);
            $table->unsignedInteger('allotment');
            $table->unsignedInteger('taxes');
            $table->unsignedInteger('services');
            $table->unsignedInteger('advance_sales');
            $table->unsignedInteger('promotions');
            $table->boolean('status');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('meal_id');
            $table->unsignedBigInteger('rates_plans_type_id');
            $table->unsignedBigInteger('charge_type_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('room_id')->references('id')->on('rooms');
            $table->foreign('meal_id')->references('id')->on('meals');
            $table->foreign('rates_plans_type_id')->references('id')->on('rates_plans_types');
            $table->foreign('charge_type_id')->references('id')->on('charge_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rates_plans');
    }
}
