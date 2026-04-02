<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPoliciesCancellationReservationsServicesRatesPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_services_rates_plans', function (Blueprint $table) {
            $table->text('policies_cancellation')->after('infant_num');
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
            $table->text('policies_cancellation');
        });
    }
}
