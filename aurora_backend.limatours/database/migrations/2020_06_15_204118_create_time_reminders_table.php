<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimeRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_reminders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('category_reminders');
            $table->smallInteger('time')->comment('1, 3, 5, 7, 15, 30, 45, etc');
            $table->string('format', 255)->comment('days, week, month, etc..');
            $table->unsignedBigInteger('reminder_type_id');
            $table->foreign('reminder_type_id')->references('id')->on('reminder_types');
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
        Schema::dropIfExists('time_reminders');
    }
}
