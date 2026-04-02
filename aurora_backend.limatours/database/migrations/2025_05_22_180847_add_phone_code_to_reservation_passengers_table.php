<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneCodeToReservationPassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_passengers', function (Blueprint $table) {
            Schema::table('reservation_passengers', function (Blueprint $table) {
            $table->string('phone_code', 10)
                  ->nullable()
                  ->after('phone');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_passengers', function (Blueprint $table) {
            $table->dropColumn('phone_code');
        });
    }
}
