<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateHotelClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('period');
            $table->decimal('markup', 3, 2);
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('hotel_id');
            $table->foreign("client_id")->references('id')->on('clients');
            $table->foreign("hotel_id")->references('id')->on('hotels');
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
        Schema::dropIfExists('hotel_clients');
    }
}
