<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionExpediaReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_expedia_reserves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserve_file_id');
            $table->foreign('reserve_file_id')->references('id')->on('reserve_files');
            $table->unsignedBigInteger('expedia_service_id');
            $table->foreign('expedia_service_id')->references('id')->on('extension_expedia_services');
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
        Schema::dropIfExists('extension_expedia_reserves');
    }
}
