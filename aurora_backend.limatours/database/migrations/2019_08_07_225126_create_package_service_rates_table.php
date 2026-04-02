<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageServiceRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_service_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('package_service_id');
            $table->foreign('package_service_id')->references('id')->on('package_services');
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
        Schema::dropIfExists('package_service_rates');
    }
}
