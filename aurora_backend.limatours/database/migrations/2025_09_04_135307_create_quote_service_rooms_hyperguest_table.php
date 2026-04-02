<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuoteServiceRoomsHyperguestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_service_rooms_hyperguest', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quote_service_id');
            $table->foreign('quote_service_id')->references('id')->on('quote_services');
            $table->unsignedBigInteger('room_id');
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->unsignedBigInteger('rate_plan_id');
            $table->foreign('rate_plan_id')->references('id')->on('rates_plans');            
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
        Schema::dropIfExists('quote_service_rooms_hyperguest');
    }
}
