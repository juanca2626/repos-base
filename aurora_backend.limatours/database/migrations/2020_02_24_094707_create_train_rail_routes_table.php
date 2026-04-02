<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainRailRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_rail_routes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('train_id');
            $table->unsignedBigInteger('rail_route_id');
            $table->string('name');
            $table->string('code',45);
            $table->string('abbreviation',45)->nullable();
            $table->foreign('train_id')->references('id')->on('trains');
            $table->foreign('rail_route_id')->references('id')->on('rail_routes');
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
        Schema::dropIfExists('train_rail_routes');
    }
}
