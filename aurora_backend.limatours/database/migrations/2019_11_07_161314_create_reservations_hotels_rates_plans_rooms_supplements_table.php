<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsHotelsRatesPlansRoomsSupplementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_hotels_rates_plans_rooms_supplements', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('reservations_hotels_rates_plans_rooms_id');
            $table->bigInteger('supplement_id');
            $table->float('total_amount');
            $table->text('supplement');
            $table->integer('amount_extra');
            $table->text('type');

//            $table->foreign('reservations_hotels_rates_plans_rooms_id','res_room_id')->references('id')->on('reservations_hotels_rates_plans_rooms');
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
        Schema::dropIfExists('reservations_hotels_rates_plans_rooms_supplements');
    }
}
