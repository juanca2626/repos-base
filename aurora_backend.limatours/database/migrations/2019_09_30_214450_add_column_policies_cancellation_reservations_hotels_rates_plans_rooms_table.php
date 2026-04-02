<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPoliciesCancellationReservationsHotelsRatesPlansRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_hotels_rates_plans_rooms', function (Blueprint $table) {
            $table->text('policies_cancellation')->after('first_penalty_date');
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
            $table->dropColumn('policies_cancellation');
        });
    }
}
