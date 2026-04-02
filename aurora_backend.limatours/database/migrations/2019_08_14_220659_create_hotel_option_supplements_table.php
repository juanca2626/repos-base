<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelOptionSupplementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_option_supplements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('from');
            $table->date('to');
            $table->float('price_per_room')->default(0);
            $table->float('price_per_person')->default(0);
            $table->tinyInteger('min_age')->default(0);
            $table->tinyInteger('max_age')->default(0);
            $table->timestamps();

            $table->unsignedBigInteger('hotel_id');
            $table->foreign('hotel_id')->references('id')->on('hotels');

            $table->unsignedBigInteger('supplement_id');
            $table->foreign('supplement_id')->references('id')->on('suplements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hotel_option_supplements');
    }
}
