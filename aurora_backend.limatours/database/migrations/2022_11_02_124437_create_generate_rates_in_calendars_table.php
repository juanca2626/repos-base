<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGenerateRatesInCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generate_rates_in_calendars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('rates_plans_id');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->integer('perido');
            $table->boolean('status');
            $table->text('status_message')->nullable();
            $table->unsignedBigInteger('user_add');
            $table->timestamps();

            $table->foreign('hotel_id')->references('id')->on('hotels');
            $table->foreign('rates_plans_id')->references('id')->on('rates_plans');
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->foreign('user_add')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generate_rates_in_calendars');
    }
}
