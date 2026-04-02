<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsReservationsServicesRatesPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_services_rates_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('executive_id')->after('reservations_service_id');
            $table->date('date')->after('service_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_services_rates_plans', function (Blueprint $table) {
            $table->dropColumn(['executive_id']);
            $table->dropColumn(['date']);
        });
    }
}
