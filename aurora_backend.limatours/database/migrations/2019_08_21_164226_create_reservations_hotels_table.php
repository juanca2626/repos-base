<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_hotels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('reservations_discount_id')->nullable();
            $table->unsignedBigInteger('reservations_extra_charge_id')->nullable();

            $table->tinyInteger('status')->comment('Los posibles valores de este campo son\n\n- 0 = inactivo (este registro fue eliminado de la reserva)\n- 1 = activo (este registro esta activo en la reserva)\n- 2 = por confirmar (el elemento esta activo en la reserva pero aun no se confirma su validez).');
            $table->tinyInteger('status_in_channel')->comment('Los posibles valores de este campo son\n\n- 0 = inactivo (este registro fue eliminado de la reserva)\n- 1 = activo (este registro esta activo en la reserva)\n- 2 = por confirmar (el elemento esta activo en la reserva pero aun no se confirma su validez).');

            $table->enum('reservator_type', ['excecutive', 'client']);
            $table->unsignedBigInteger('executive_id');
            $table->text('executive_name');

            $table->unsignedBigInteger('chain_id');
            $table->unsignedBigInteger('hotel_id');
            $table->text('hotel_code');
            $table->text('hotel_name');

            $table->date('check_in');
            $table->date('check_out');
            $table->time('check_in_time');
            $table->time('check_out_time');

            $table->tinyInteger('nights');
            $table->tinyInteger('payment_status')->default(0);

            $table->float('total_amount', 10, 2)->default(0.00);
            $table->float('total_amount_base', 10, 2)->default(0.00);

            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('update_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->foreign('reservation_id','res_hot_id')->references('id')->on('reservations');
            $table->foreign('reservations_discount_id','res_hot_res_dis_id')->references('id')->on('reservations_discounts');
            $table->foreign('reservations_extra_charge_id','res_hot_res_ext_char_id')->references('id')->on('reservations_extra_charges');

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
        Schema::dropIfExists('reservations_hotels');
    }
}
