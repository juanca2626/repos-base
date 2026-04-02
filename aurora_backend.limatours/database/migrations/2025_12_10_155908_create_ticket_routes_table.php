<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_routes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ticket_circuit_id')->unsigned();
            $table->string('name', 45);
            $table->timestamps();

            // Relación con la tabla circuits
            $table->foreign('ticket_circuit_id')->references('id')->on('ticket_circuits')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_routes');
    }
}
