<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('year');
            $table->longText('remarks')->nullable();
            $table->longText('notes')->nullable();
            $table->unsignedBigInteger('language_id');
            $table->unsignedBigInteger('hotel_id');
            $table->foreign('language_id')->references('id')->on('languages');            
            $table->foreign('hotel_id')->references('id')->on('hotels');  
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
        Schema::dropIfExists('hotel_alerts');
    }
}
