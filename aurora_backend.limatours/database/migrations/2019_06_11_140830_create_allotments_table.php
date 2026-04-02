<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllotmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allotments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('num_days_blocked')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('rate_plan_rooms_id');

            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('rate_plan_rooms_id')->references('id')->on('rates_plans_rooms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allotments');
    }
}
