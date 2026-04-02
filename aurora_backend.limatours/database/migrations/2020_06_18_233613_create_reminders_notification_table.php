<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRemindersNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminder_notification', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255);
            $table->date('fecini');
            $table->date('fecfin');
            $table->text('users');
            $table->text('content');
            $table->char('type', 1)->comment('1 => Semanal, 2 => Diario');
            $table->char('priority', 1)->comment('B => Baja, M => Media, A => Alta');
            $table->string('time', 8);
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
        Schema::dropIfExists('reminders_notification');
    }
}
