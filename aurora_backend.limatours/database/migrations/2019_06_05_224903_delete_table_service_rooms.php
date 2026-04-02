<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteTableServiceRooms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('service_rooms');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('service_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('max_capacity');
            $table->integer('min_adults');
            $table->integer('max_adults');
            $table->integer('max_child');
            $table->integer('max_infants');
            $table->integer('min_inventory');
            $table->boolean('state');
            $table->string('aurora_code')->nullable();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('room_type_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('room_type_id')->references('id')->on('room_types');
        });
    }
}
