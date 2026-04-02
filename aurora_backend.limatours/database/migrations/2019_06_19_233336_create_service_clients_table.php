<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('period');
            $table->decimal('markup', 3, 2);
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('service_id');
            $table->foreign("client_id")->references('id')->on('clients');
            $table->foreign("service_id")->references('id')->on('services');
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
        Schema::dropIfExists('service_clients');
    }
}
