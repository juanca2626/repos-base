<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_reminders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255)->comment('vip, paxs, etc..');
            $table->unsignedBigInteger('reminder_id');
            $table->foreign('reminder_id')->references('id')->on('reminders');
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
        Schema::dropIfExists('category_reminders');
    }
}
