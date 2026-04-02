<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageDestinationDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_destination_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('day_number')->nullable();
            $table->tinyInteger('order')->nullable();
            $table->tinyInteger('nights')->nullable()->comment('SOLO HOTEL');
            $table->unsignedBigInteger('package_destinations_id');
            $table->foreign('package_destinations_id')->references('id')->on('package_destinations');
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
        Schema::dropIfExists('package_destination_days');
    }
}
