<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPaxReservationsServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_services', function (Blueprint $table) {
            $table->unsignedTinyInteger('infant_num')->after('markup')->default(0);
            $table->unsignedTinyInteger('child_num')->after('markup')->default(0);
            $table->unsignedTinyInteger('adult_num')->after('markup')->default(0);
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
            $table->dropColumn(['adult_num']);
            $table->dropColumn(['child_num']);
            $table->dropColumn(['infant_num']);
        });
    }
}
