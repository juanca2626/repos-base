<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCancellationPoliciesReservationsPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_packages', function (Blueprint $table) {
            $table->json('cancellation_policy')->nullable()->after('price_per_child_without_bed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_packages', function (Blueprint $table) {
            //
        });
    }
}
