<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsReservationsServicesRatesPlansCancellationPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_services_rates_plans_cancellation_policies', function (Blueprint $table) {
            $table->smallInteger('max_pax')->after('policy_cancelations_parameter_id');
            $table->smallInteger('min_pax')->after('policy_cancelations_parameter_id');
            $table->smallInteger('unit_duration')->after('policy_cancelations_parameter_id');
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
            $table->dropColumn('max_pax');
            $table->dropColumn('min_pax');
            $table->dropColumn('unit_duration');
        });
    }
}
