<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsFlightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_flights', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservation_id');
            $table->string('code_flight');
            $table->string('origin');
            $table->string('destiny');
            $table->date('date');
            $table->integer('adult_num');
            $table->integer('child_num');
            $table->integer('inf_num');
            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('update_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();
            // $table->foreign('reservation_id', 'res_ser_id')->references('id')->on('reservations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservations_flights');
    }
}
