<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnServiceReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->float('total_services_taxes', 10, 2)->after('total_hotels')->default(0.00);
            $table->float('total_services_subs', 10, 2)->after('total_hotels')->default(0.00);
            $table->float('total_services', 10, 2)->after('total_hotels')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['total_services_taxes']);
            $table->dropColumn(['total_services_subs']);
            $table->dropColumn(['total_services']);
        });
    }
}
