<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnHourReservationsServicesRatesPlansCancellationPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        DB::statement("ALTER TABLE `reservations_services_rates_plans_cancellation_policies`
                            CHANGE `min_hour` `min_hour` SMALLINT (11) DEFAULT 1");
        DB::statement("ALTER TABLE `reservations_services_rates_plans_cancellation_policies`
                            CHANGE `max_hour` `max_hour` SMALLINT (11) DEFAULT 1;");
        Schema::enableForeignKeyConstraints();
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
