<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumReservationsHotelsRatesPlansRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_hotels_rates_plans_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('executive_id');
            $table->text('executive_name');

            $table->text('hotel_code');
            $table->text('hotel_name');
            $table->text('room_code');

            $table->tinyInteger('status_in_channel');

            $table->text('check_in_time');
            $table->text('check_out_time');

            $table->text('channel_hotel_code');
            $table->text('channel_room_code');

            $table->text('room_name');
            $table->text('room_description');

            $table->text('rate_plan_code');
            $table->text('rate_plan_name');

            $table->date('first_penalty_date')->nullable();

            $table->tinyInteger('nights');
            $table->tinyInteger('payment_status')->default(0);

            $table->timestamp('cancel_at', 0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
