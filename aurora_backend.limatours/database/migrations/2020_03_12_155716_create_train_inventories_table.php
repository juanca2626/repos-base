<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('train_rate_id');
            $table->foreign('train_rate_id')->references('id')->on('train_rates');
            $table->tinyInteger('day');
            $table->date('date');
            $table->integer('inventory_num');
            $table->integer('total_booking');
            $table->integer('total_canceled');
            $table->boolean('locked');
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
        Schema::dropIfExists('train_inventories');
    }
}
