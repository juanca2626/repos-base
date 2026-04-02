<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// @codingStandardsIgnoreLine
class CreateClientRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_rate_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('period');
            $table->decimal('markup', 10, 2);
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('rate_plan_id');
            $table->foreign('client_id')->references('id')->on('clients');
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
        Schema::dropIfExists('client_rate_plans');
    }
}
