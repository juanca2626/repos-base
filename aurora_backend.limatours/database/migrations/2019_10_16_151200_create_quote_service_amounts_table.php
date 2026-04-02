<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuoteServiceAmountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_service_amounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('amount',8,2);

            $table->unsignedBigInteger('quote_service_id');
            $table->foreign('quote_service_id')->references('id')->on('quote_services');

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
        Schema::dropIfExists('quote_service_amounts');
    }
}
