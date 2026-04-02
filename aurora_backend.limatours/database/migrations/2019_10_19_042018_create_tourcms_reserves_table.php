<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourcmsReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tourcms_reserves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_file_id');
            $table->foreign('reserve_file_id')->references('id')->on('reserve_files');
            $table->unsignedBigInteger('tourcms_channel_id');
            $table->foreign('tourcms_channel_id')->references('id')->on('tourcms_channels');
            $table->integer('booking_id');
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
        Schema::dropIfExists('tourcms_reserves');
    }
}
