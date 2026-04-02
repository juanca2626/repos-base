<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDataToReservationPassengers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        DB::statement("ALTER TABLE `reservation_passengers`
                            CHANGE `document_number` `document_number` BIGINT(20) UNSIGNED NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `reservation_passengers`
                            CHANGE `name` `name` VARCHAR(255) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `reservation_passengers`
                            CHANGE `surnames` `surnames` VARCHAR(255) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `reservation_passengers`
                            CHANGE `date_birth` `date_birth` DATE NULL DEFAULT NULL");
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_passengers', function (Blueprint $table) {

        });
    }
}
