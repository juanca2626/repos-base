<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservation_id');
            $table->enum('reservator_type', ['excecutive', 'client']);
            $table->unsignedBigInteger('executive_id');
            $table->text('executive_name');

            $table->unsignedBigInteger('service_id');
            $table->text('service_code');
            $table->text('service_name');
            $table->date('date');
            $table->time('time');
            $table->tinyInteger('payment_status')->default(0);
            $table->float('total_amount', 10, 2)->default(0.00);
            $table->float('total_amount_base', 10, 2)->default(0.00);

            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('update_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();
            $table->tinyInteger('status')->comment('Los posibles valores de este campo son\n\n- 0 = inactivo (este registro fue eliminado de la reserva)\n- 1 = activo (este registro esta activo en la reserva)\n- 2 = por confirmar (el elemento esta activo en la reserva pero aun no se confirma su validez).');
            $table->foreign('reservation_id', 'res_ser_id')->references('id')->on('reservations');
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
        Schema::dropIfExists('reservations_services');
    }
}
