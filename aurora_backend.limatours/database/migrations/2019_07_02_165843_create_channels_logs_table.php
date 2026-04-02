<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channels_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->text('request_ip');
            $table->text('request_headers');
            $table->text('response_headers');
            $table->text('log_directory');
            $table->text('echo_token');
            $table->text('token');

            $table->text('date');

            $table->tinyInteger('status');

            $table->unsignedBigInteger('channel_id');

            $table->foreign('channel_id')->references('id')->on('channels');
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
        Schema::dropIfExists('channels_logs');
    }
}
