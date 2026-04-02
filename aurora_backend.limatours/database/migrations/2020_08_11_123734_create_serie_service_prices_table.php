<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerieServicePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serie_service_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('serie_service_id');
            $table->foreign('serie_service_id')->references('id')->on('serie_services');
            $table->unsignedBigInteger('serie_range_id');
            $table->foreign('serie_range_id')->references('id')->on('serie_ranges');
            $table->string('base_code', 10)->nullable();
            $table->double('amount')->nullable();
            $table->string('amount_type', 20)->nullable();
            $table->tinyInteger('amount_recalculated')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serie_service_prices');
    }
}
