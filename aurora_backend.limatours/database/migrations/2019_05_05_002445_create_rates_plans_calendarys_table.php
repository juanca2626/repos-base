<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateRatesPlansCalendarysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rates_plans_calendarys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->unsignedBigInteger('rates_plan_id');
            $table->unsignedBigInteger('policies_rate_id');
            $table->boolean('status');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('rates_plan_id')->references('id')->on('rates_plans');
            $table->foreign('policies_rate_id')->references('id')->on('policies_rates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rates_plans_calendarys');
    }
}
