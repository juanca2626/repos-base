<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToReservationsHotelsRatesPlansRoomsCalendarysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_hotels_rates_plans_rooms_calendarys', function (Blueprint $table) {
            $table->boolean('update_inventory_reserve')->default(0)->after('total_amount_base')->comment('campo para saber si la reserva se actualizo en el inventario');
            $table->boolean('update_inventory_cancelled')->default(0)->after('update_inventory_reserve')->comment('campo para saber si la reserva cancelada se actualizo en el inventario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_hotels_rates_plans_rooms_calendarys', function (Blueprint $table) {
            $table->dropColumn('update_inventory_reserve');
            $table->dropColumn('update_inventory_cancelled');
        });
    }
}
