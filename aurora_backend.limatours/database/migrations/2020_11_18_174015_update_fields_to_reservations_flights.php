<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFieldsToReservationsFlights extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_flights', function (Blueprint $table) {
            $table->string('origin')->nullable()->change();
            $table->string('destiny')->nullable()->change();
            $table->date('date')->nullable()->change();
            $table->integer('adult_num')->nullable()->change();
            $table->integer('child_num')->nullable()->change();
            $table->integer('inf_num')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_flights', function (Blueprint $table) {
            //
        });
    }
}
