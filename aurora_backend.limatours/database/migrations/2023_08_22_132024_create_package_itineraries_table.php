<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageItinerariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_itineraries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('year');
            $table->string('itinerary_link');
            $table->string('itinerary_link_commercial')->nullable();
            $table->string('link_itinerary_priceless');            
            $table->unsignedBigInteger('package_id');
            $table->foreign('package_id')->references('package_id')->on('package_translations');
            $table->unsignedBigInteger('language_id');
            $table->foreign('language_id')->references('id')->on('languages');
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
        Schema::dropIfExists('package_itineraries');
    }
}
