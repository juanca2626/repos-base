<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpeNotificationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ope_notification_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ope_notification_id');
            $table->foreign('ope_notification_id')->references('id')->on('ope_notifications');
            $table->enum('type', ['email', 'whatsapp']);
            $table->integer('pax');
            $table->string('message_id')->nullable();
            $table->string('message')->nullable();
            $table->smallInteger('status');
            $table->string('status_notification')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('ope_notification_logs');
    }
}
