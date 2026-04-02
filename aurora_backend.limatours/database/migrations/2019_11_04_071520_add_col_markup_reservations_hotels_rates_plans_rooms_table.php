<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColMarkupReservationsHotelsRatesPlansRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_hotels_rates_plans_rooms', function (Blueprint $table) {
            $table->float('markup', 9, 2)->after('payment_status')->nullable();
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
            $table->dropColumn('markup');
        });
    }
}
