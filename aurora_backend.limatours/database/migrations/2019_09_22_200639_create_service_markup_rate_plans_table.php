<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceMarkupRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_markup_rate_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('markup', 10, 2);
            $table->integer('period');
            $table->unsignedBigInteger('client_id')->unsigned()->index();
            $table->unsignedBigInteger('service_rate_id')->unsigned()->index();
            $table->foreign('service_rate_id')->references('id')->on('service_rates');
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
        Schema::dropIfExists('service_markup_rate_plans');
    }
}
