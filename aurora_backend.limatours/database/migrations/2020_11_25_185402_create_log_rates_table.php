<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('year');
            $table->unsignedBigInteger('user_id');
            $table->string('user_email');
            $table->unsignedBigInteger('client_id');
            $table->string('client_code');
            $table->timestamps();

            $table->foreign("user_id")->references('id')->on('users');
            $table->foreign("client_id")->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_rates');
    }
}
