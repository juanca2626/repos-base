<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableColumnsReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `reservations` CHANGE COLUMN `given_name` `given_name` VARCHAR(50) NULL ;");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `reservations` CHANGE COLUMN `surname` `surname` VARCHAR(50) NULL ;");
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
            //
        });
    }
}
