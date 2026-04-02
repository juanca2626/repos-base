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
            $table->text('service_rate_name')->after('service_rate_plan_id');
            $table->unsignedBigInteger('service_rate_id')->after('service_rate_plan_id');
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
            $table->dropColumn('service_rate_plan_name');
            $table->dropColumn('service_rate_id');
        });
    }
}
