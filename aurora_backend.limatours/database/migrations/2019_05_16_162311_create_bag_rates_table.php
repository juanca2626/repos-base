<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBagRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bag_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bag_id');
            $table->unsignedBigInteger('rate_plan_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bag_id')->references('id')->on('bags');
            $table->foreign('rate_plan_id')->references('id')->on('rates_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bag_rates');
    }
}
