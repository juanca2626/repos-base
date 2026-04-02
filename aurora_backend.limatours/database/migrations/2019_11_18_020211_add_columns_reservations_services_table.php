<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsReservationsServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_services', function (Blueprint $table) {
            $table->float('total_amount_taxes', 10, 2)->after('payment_status')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_services', function (Blueprint $table) {
            $table->dropColumn(['total_amount_taxes']);
        });
    }
}
