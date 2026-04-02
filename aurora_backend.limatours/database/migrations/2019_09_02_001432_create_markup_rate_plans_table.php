<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarkupRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('markup_rate_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('markup', 10, 2);
            $table->integer('period');
            $table->unsignedBigInteger('client_id')->unsigned()->index();
            $table->unsignedBigInteger('rate_plan_id')->unsigned()->index();
            $table->foreign('rate_plan_id')->references('id')->on('rates_plans');
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
        Schema::dropIfExists('markup_rate_plans');
    }
}
