<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionGenericOtaReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_generic_ota_reserves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_file_id');
            $table->unsignedBigInteger('generic_ota_service_id');
            $table->foreign('generic_ota_service_id')->references('id')->on('extension_generic_ota_services');
            $table->timestamps();

            $table->foreign('reserve_file_id')->references('id')->on('reserve_files');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_generic_ota_reserves');
    }
}
