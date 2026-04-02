<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataBookingCodeReservationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("SET SQL_SAFE_UPDATES = 0;");
        \Illuminate\Support\Facades\DB::statement('UPDATE `reservations` SET `booking_code` =  `file_code`');
        \Illuminate\Support\Facades\DB::statement("SET SQL_SAFE_UPDATES = 1;");
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
