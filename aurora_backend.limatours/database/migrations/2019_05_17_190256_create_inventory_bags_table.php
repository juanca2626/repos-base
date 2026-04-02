<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateInventoryBagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_bags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('day');
            $table->date('date');
            $table->integer('quantity');
            $table->integer('total_booking');
            $table->integer('total_canceled');
            $table->boolean('locked');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('bag_id');

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
        Schema::dropIfExists('inventory_bags');
    }
}
