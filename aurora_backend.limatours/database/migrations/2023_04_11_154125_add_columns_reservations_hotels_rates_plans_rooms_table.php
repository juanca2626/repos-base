<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsReservationsHotelsRatesPlansRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_hotels_rates_plans_rooms', function (Blueprint $table) {
            
            $table->integer('cancel_block_email_provider')->nullable()->default(null);
            $table->integer('cancel_hotel_or_room')->nullable()->default(null);
            $table->integer('cancel_user_id')->nullable()->default(null);
            $table->dateTime('cancel_updated_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_hotels_rates_plans_rooms', function (Blueprint $table) {
            $table->dropColumn('cancel_block_email_provider');
            $table->dropColumn('cancel_hotel_or_room');
            $table->dropColumn('cancel_user_id');
            $table->dropColumn('cancel_updated_at');
        });
    }
}
