<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsHotelsRatesPlansRoomsCancellationPolliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_hotels_rates_plans_rooms_cancellation_pollicies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservations_hotels_rates_plans_rooms_id');

            $table->unsignedBigInteger('policy_cancelation_id')->comment('Es el dato igual a el campo "id" que se  consiguio en la tabla policies_cancelations la momento de la busqueda');
            $table->string('name', 150)->comment('Es el dato igual al que se consiguio en la tabla policies_cancelations la momento de la busqueda');
            $table->unsignedBigInteger('hotel_id')->comment('Es el dato igual al que se consiguio en la tabla policies_cancelations la momento de la busqueda');

            $table->unsignedBigInteger('policy_cancelations_parameter_id')->comment('Es el dato igual a el campo "id" que se  consiguio en la tabla "policy_cancellation_parameters" la momento de la busqueda');
            $table->tinyInteger('min_day')->comment('Es el dato igual al que se consiguio en la tabla "policy_cancellation_parameters" la momento de la busqueda');
            $table->tinyInteger('max_day')->comment('Es el dato igual al que se consiguio en la tabla "policy_cancellation_parameters" la momento de la busqueda');
            $table->unsignedInteger('amount')->comment('Es el dato igual al que se consiguio en la tabla "policy_cancellation_parameters" la momento de la busqueda');
            $table->tinyInteger('tax')->comment('Es el dato igual al que se consiguio en la tabla "policy_cancellation_parameters" la momento de la busqueda');
            $table->tinyInteger('service')->comment('Es el dato igual al que se consiguio en la tabla "policy_cancellation_parameters" la momento de la busqueda');
            $table->unsignedBigInteger('penalty_id')->nullable()->comment('Es el dato igual al que se consiguio en la tabla "policy_cancellation_parameters" la momento de la busqueda');

            $table->string('penalty_name', 150)->comment('Es el dato igual al campo "name" que se consiguio en la tabla "penalties" correspondiente a "id" = "penalty_id" la momento de la busqueda');

            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('update_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->foreign('reservations_hotels_rates_plans_rooms_id','res_hot_rat_roo_can_pol_res_hot_rat_roo_id')->references('id')->on('reservations_hotels_rates_plans_rooms');

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
        Schema::dropIfExists('reservations_hotels_rates_plans_rooms_cancellation_pollicies');
    }
}
