<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('max_capacity');
            $table->integer('min_adults');
            $table->integer('max_adults');
            $table->integer('max_child');
            $table->integer('max_infants');
            $table->integer('min_inventory');
            $table->boolean('state');
            $table->string('aurora_code')->nullable();
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('room_type_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hotel_id')->references('id')->on('hotels');
            $table->foreign('room_type_id')->references('id')->on('room_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
}
