<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsHotelsRatesPlansRoomsCalendarysRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_hotels_rates_plans_rooms_calendarys_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservations_hotels_rates_plans_rooms_calendarys_id');

            $table->unsignedBigInteger('rate_id')->comment('Es el dato igual a el campo "id" que se  consiguio en la tabla "rates" la momento de la busqueda');
            $table->unsignedBigInteger('rates_plans_calendarys_id')->comment('Es el dato igual al que se consiguio en la tabla "rates" la momento de la busqueda');

            $table->tinyInteger('num_adult')->comment('Es el dato igual al que se consiguio en la tabla "rates" la momento de la busqueda');
            $table->tinyInteger('num_child')->default(0)->comment('Es el dato igual al que se consiguio en la tabla "rates" la momento de la busqueda');
            $table->tinyInteger('num_infant')->default(0)->comment('Es el dato igual al que se consiguio en la tabla "rates" la momento de la busqueda');

            $table->float('price_adult',10,2)->comment('Es el dato igual al que se consiguio en la tabla "rates" mas el porcentaje del cliente "marckup" la momento de la busqueda');
            $table->float('price_child',10,2)->default(0)->comment('Es el dato igual al que se consiguio en la tabla "rates" mas el porcentaje del cliente "marckup" la momento de la busqueda');
            $table->float('price_infant',10,2)->default(0)->comment('Es el dato igual al que se consiguio en la tabla "rates" mas el porcentaje del cliente "marckup" la momento de la busqueda');
            $table->float('price_extra',10,2)->default(0)->comment('Es el dato igual al que se consiguio en la tabla "rates" mas el porcentaje del cliente "marckup" la momento de la busqueda');
            $table->float('price_total',10,2)->default(0)->comment('Es el dato igual al que se consiguio en la tabla "rates" mas el porcentaje del cliente "marckup" la momento de la busqueda');

            $table->float('price_adult_base',10,2)->comment('Es el dato igual a "price_adult" que se consiguio en la tabla "rates" la momento de la busqueda');
            $table->float('price_child_base',10,2)->default(0)->comment('Es el dato igual a "price_child" que se consiguio en la tabla "rates" la momento de la busqueda');
            $table->float('price_infant_base',10,2)->default(0)->comment('Es el dato igual a "price_infant" que se consiguio en la tabla "rates" la momento de la busqueda');
            $table->float('price_extra_base',10,2)->default(0)->comment('Es el dato igual a "price_extra" que se consiguio en la tabla "rates" la momento de la busqueda');
            $table->float('price_total_base',10,2)->default(0)->comment('Es el dato igual a "price_total" que se consiguio en la tabla "rates" la momento de la busqueda');

            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('update_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->foreign('reservations_hotels_rates_plans_rooms_calendarys_id','res_hot_rat_roo_cal_rat_res_hot_rat_roo_cal_id')->references('id')->on('reservations_hotels_rates_plans_rooms_calendarys');

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
        Schema::dropIfExists('reservations_hotels_rates_plans_rooms_calendarys_rates');
    }
}
