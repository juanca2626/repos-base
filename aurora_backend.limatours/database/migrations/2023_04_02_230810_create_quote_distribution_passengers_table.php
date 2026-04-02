<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuoteDistributionPassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_distribution_passengers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quote_distribution_id');
            $table->unsignedBigInteger('quote_passenger_id');
            $table->timestamps();
            $table->foreign('quote_distribution_id')->references('id')->on('quote_distributions')->onDelete('cascade');
            $table->foreign('quote_passenger_id')->references('id')->on('quote_passengers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_distribution_passengers');
    }
}
