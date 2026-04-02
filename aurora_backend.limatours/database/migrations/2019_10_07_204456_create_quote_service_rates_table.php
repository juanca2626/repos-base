<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuoteServiceRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_service_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quote_service_id');
            $table->foreign('quote_service_id')->references('id')->on('quote_services');
            $table->unsignedBigInteger('service_rate_id');
            $table->foreign('service_rate_id')->references('id')->on('service_rates');
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
        Schema::dropIfExists('quote_service_rates');
    }
}
