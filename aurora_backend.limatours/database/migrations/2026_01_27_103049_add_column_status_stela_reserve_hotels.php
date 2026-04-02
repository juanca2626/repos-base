<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStatusStelaReserveHotels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_hotels', function (Blueprint $table) {
            $table->smallInteger('status_stela_reserve')->default(0)->after('status_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_hotels', function (Blueprint $table) {
            $table->dropColumn('status_stela_reserve');
        });
    }
}
