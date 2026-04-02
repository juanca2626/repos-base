<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_rate_plans', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('service_rate_id')->unsigned()->index();
            $table->foreign('service_rate_id')->references('id')->on('service_rates')->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->date('date_from');
            $table->date('date_to');

            $table->softDeletes();
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
        Schema::dropIfExists('service_rate_plans');
    }
}
