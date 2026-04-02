<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientMailingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_mailing', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('clients_id');
            $table->boolean('weekly');
            $table->boolean('day_before');
            $table->boolean('daily');
            $table->boolean('survey');
            $table->boolean('status');
            $table->foreign('clients_id')->references('id')->on('clients');
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
        Schema::dropIfExists('client_mailing');
    }
}
