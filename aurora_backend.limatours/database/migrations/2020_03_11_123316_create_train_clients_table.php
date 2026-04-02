<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('period');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('train_template_id');
            $table->foreign("client_id")->references('id')->on('clients');
            $table->foreign("train_template_id")->references('id')->on('train_templates');
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
        Schema::dropIfExists('train_clients');
    }
}
