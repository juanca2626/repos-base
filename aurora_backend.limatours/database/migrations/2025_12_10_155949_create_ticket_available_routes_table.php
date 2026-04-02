<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketAvailableRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_available_routes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ticket_route_id')->unsigned();
            $table->date('date');
            $table->timestamps();

            // Relación con la tabla routes
            $table->foreign('ticket_route_id')->references('id')->on('ticket_routes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_available_routes');
    }
}
