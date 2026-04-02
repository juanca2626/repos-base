<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsExtraChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_extra_charges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservation_id');

            $table->unsignedBigInteger('hotel_taxe_id')->comment('Es el dato igual a el campo "id" que se  consiguio en la tabla "hotel_taxe" la momento de la busqueda');
            $table->float('amount',10,2)->comment('Es el dato igual al que se consiguio en la tabla "hotel_taxe" la momento de la busqueda');
            $table->tinyInteger('status')->comment('Es el dato igual al que se consiguio en la tabla "hotel_taxe" la momento de la busqueda');
            $table->unsignedBigInteger('hotel_id')->comment('Es el dato igual al que se consiguio en la tabla "hotel_taxe" la momento de la busqueda');
            $table->unsignedBigInteger('tax_id')->comment('Es el dato igual al que se consiguio en la tabla "hotel_taxe" la momento de la busqueda');

            $table->unsignedBigInteger('country_id')->comment('Es el dato igual al que se consiguio en la tabla "taxe" la momento de la busqueda');
            $table->string('name',150)->comment('Es el dato igual al que se consiguio en la tabla "taxe" la momento de la busqueda');
            $table->float('value',10,2)->comment('Es el dato igual al que se consiguio en la tabla "taxe" la momento de la busqueda');
            $table->enum('type',['t', 's'])->comment('Es el dato igual al que se consiguio en la tabla "taxe" la momento de la busqueda');

            $table->float('total_hotels', 10, 2)->default(0.00)->comment('Esta es la suma de todos los hoteles de la reserva \"reservation_id\" y de la misma cadena \"chain_id\", los hoteles a los quie aplicara son los hoteles que son registrados en la reserva en un mismo tiempo dado');
            $table->float('total_extra_charge', 10, 2)->default(0.00)->comment('Este es la diferencia resultante de restar \"total_hotels\" menos el porcentaje indicado en \"percent_discount\"');

            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('update_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->foreign('reservation_id')->references('id')->on('reservations');

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
        Schema::dropIfExists('reservations_extra_charges');
    }
}
