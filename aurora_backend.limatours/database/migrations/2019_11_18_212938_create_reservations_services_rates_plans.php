<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsServicesRatesPlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_services_rates_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservations_service_id');
            $table->tinyInteger('status')->comment('Los posibles valores de este campo son\n\n- 0 = inactivo (este registro fue eliminado de la reserva)\n- 1 = activo (este registro esta activo en la reserva)\n- 2 = por confirmar (el elemento esta activo en la reserva pero aun no se confirma su validez).');
            $table->unsignedBigInteger('service_id');
            $table->text('service_name');
            $table->unsignedBigInteger('service_rate_plan_id');
            $table->date('date_from');
            $table->date('date_to');
            $table->integer('pax_from');
            $table->integer('pax_to');
            $table->float('price_adult');
            $table->float('price_child');
            $table->float('price_infant');
            $table->float('price_guide');
            $table->unsignedTinyInteger('adult_num')->default(0);
            $table->unsignedTinyInteger('child_num')->default(0);
            $table->unsignedTinyInteger('infant_num')->default(0);
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
        Schema::dropIfExists('reservations_services_rates_plans');
    }
}
