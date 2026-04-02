<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStellaUpdatedAtToReservationsHotelsRatesPlansRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_hotels_rates_plans_rooms', function (Blueprint $table) {
            $table->dateTime('stella_updated_at')->nullable();
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
            $table->removeColumn(['stella_updated_at']);
        });
    }
}
