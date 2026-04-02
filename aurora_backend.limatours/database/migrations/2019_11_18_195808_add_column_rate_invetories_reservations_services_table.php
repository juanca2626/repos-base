<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnRateInvetoriesReservationsServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_services', function (Blueprint $table) {
            $table->unsignedBigInteger('service_rate_id')->after('service_id');
            $table->unsignedBigInteger('service_inventory_id')->after('service_id');
            $table->float('markup',8,2)->after('total_amount_base');
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
            $table->dropColumn(['service_rate_id']);
            $table->dropColumn(['service_inventory_id']);
            $table->dropColumn(['markup']);
        });
    }
}
