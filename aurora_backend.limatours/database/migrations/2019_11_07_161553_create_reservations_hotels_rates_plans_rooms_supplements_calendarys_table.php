<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsHotelsRatesPlansRoomsSupplementsCalendarysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_hotels_rates_plans_rooms_supplements_calendarys', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('reservations_hotels_rates_plans_rooms_supplements_id');
            $table->bigInteger('calendary_id');
            $table->date('date');
            $table->float('nuprice_per_room');
            $table->float('price_per_person');
            $table->integer('min_age');
            $table->integer('max_age');

//            $table->foreign('reservations_hotels_rates_plans_rooms_supplements_id','res_room_sup_id')->references('id')->on('reservations_hotels_rates_plans_rooms_supplements');

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
        Schema::dropIfExists('reservations_hotels_rates_plans_rooms_supplements_calendarys');
    }
}
