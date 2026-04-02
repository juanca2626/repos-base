<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketAvailableRouteTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('ticket_available_route_times', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ticket_available_route_id')->unsigned();
            $table->time('time');
            $table->integer('ticket_quantity');
            $table->timestamps();

            // Relación con la tabla available_routes
            $table->foreign('ticket_available_route_id')->references('id')->on('ticket_available_routes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_available_route_times');
    }
}
