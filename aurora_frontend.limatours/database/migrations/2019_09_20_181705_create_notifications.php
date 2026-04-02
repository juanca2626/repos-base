<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('target', 1)->comment('1 => Frontend, 2 => Backend, 3 => Ambos');
            $table->string('title', 255);
            $table->text('content');
            $table->char('type', 1)->comment('1 => Enlace directo, 2 => Funcion JS');
            $table->string('url', 255);
            $table->char('user', 6);
            $table->char('status', 1)->comment('0 => Oculto, 1 => Pendiente, 2 => Leido');
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
        Schema::dropIfExists('notifications');
    }
}
