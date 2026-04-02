<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionGygReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_gyg_reserves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_file_id');
            $table->foreign('reserve_file_id')->references('id')->on('reserve_files');
            $table->unsignedBigInteger('gyg_service_id');
            $table->foreign('gyg_service_id')->references('id')->on('extension_gyg_services');
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
        Schema::dropIfExists('extension_gyg_reserves');
    }
}
