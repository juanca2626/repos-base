<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsHotelsRatesPlansRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_hotels_rates_plans_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('reservations_discount_id')->nullable();
            $table->unsignedBigInteger('reservations_extra_charge_id')->nullable();

            $table->tinyInteger('status')->comment('Los posibles valores de este campo son\n\n- 0 = inactivo (este registro fue eliminado de la reserva)\n- 1 = activo (este registro esta activo en la reserva)\n- 2 = por confirmar (el elemento esta activo en la reserva pero aun no se confirma su validez).');

            $table->unsignedBigInteger('chain_id');
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('rates_plan_id');
            $table->unsignedBigInteger('rates_plans_room_id');
            $table->unsignedBigInteger('channel_id');

            $table->date('check_in');
            $table->date('check_out');

            $table->unsignedBigInteger('room_type_id');
            $table->unsignedTinyInteger('max_capacity')->nullable();
            $table->unsignedTinyInteger('min_adults')->nullable();
            $table->unsignedTinyInteger('max_adults')->nullable();
            $table->unsignedTinyInteger('max_child')->nullable();
            $table->unsignedTinyInteger('max_infants')->nullable();

            $table->string('channel_reservation_code', 100)->nullable();
            $table->string('guest_note', 250)->nullable();

            $table->unsignedTinyInteger('adult_num')->default(0);
            $table->unsignedTinyInteger('child_num')->default(0);
            $table->unsignedTinyInteger('extra_num')->default(0);

            $table->float('total_amount', 10, 2)->default(0.00);
            $table->float('total_amount_base', 10, 2)->default(0.00);

            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('update_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();


            $table->foreign('reservation_id','res_hot_rat_roo_res_id')->references('id')->on('reservations');
            $table->foreign('reservations_discount_id','res_hot_rat_roo_res_dis_id')->references('id')->on('reservations_discounts');
            $table->foreign('reservations_extra_charge_id','res_hot_rat_roo_res_ext_char_id')->references('id')->on('reservations_extra_charges');

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
        Schema::dropIfExists('reservations_hotels_rates_plans_rooms');
    }
}
