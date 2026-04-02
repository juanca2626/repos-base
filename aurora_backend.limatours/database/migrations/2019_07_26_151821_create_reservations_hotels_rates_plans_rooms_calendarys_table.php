<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsHotelsRatesPlansRoomsCalendarysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_hotels_rates_plans_rooms_calendarys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservations_hotels_rates_plans_rooms_id');

            $table->unsignedBigInteger('rates_plans_calendary_id')->comment('Es el dato igual a el campo "id" que se  consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');
            $table->unsignedBigInteger('rates_plans_room_id')->comment('Es el dato igual al que se consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');
            $table->unsignedBigInteger('policies_rate_id')->nullable()->comment('Es el dato igual al que se consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');
            $table->unsignedBigInteger('policies_cancelation_id')->nullable()->comment('Es el dato igual al que se consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');

            $table->date('date')->comment('Es el dato igual al que se consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');

            $table->tinyInteger('status')->comment('Es el dato igual al que se consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');
            $table->tinyInteger('max_ab_offset')->nullable()->comment('Es el dato igual al que se consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');
            $table->tinyInteger('min_ab_offset')->nullable()->comment('Es el dato igual al que se consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');
            $table->tinyInteger('min_length_stay')->nullable()->comment('Es el dato igual al que se consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');
            $table->tinyInteger('max_length_stay')->nullable()->comment('Es el dato igual al que se consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');
            $table->tinyInteger('max_occupancy')->nullable()->comment('Es el dato igual al que se consiguio en la tabla "rates_plans_calendarys" la momento de la busqueda');

            $table->text('policies_rates')->nullable()->comment('contiene un json son las politicas delacionadas al registro correspondiente en "rates_plans_calendarys".policies_rate_id al momento de la busqueda');

            $table->float('total_amount', 10, 2)->default(0.00);
            $table->float('total_amount_base', 10, 2)->default(0.00);

            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('update_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->foreign('reservations_hotels_rates_plans_rooms_id','res_hot_rat_roo_cal_res_ho_rat_roo_id_foreign')->references('id')->on('reservations_hotels_rates_plans_rooms');

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
        Schema::dropIfExists('reservations_hotels_rates_plans_rooms_calendarys');
    }
}
