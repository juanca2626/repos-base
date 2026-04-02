<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpeNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ope_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('file');
            $table->string('service');
            $table->longText('tags')->nullable();
            $table->smallInteger('status');
            $table->bigInteger('template_id');
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
        Schema::dropIfExists('ope_notifications');
    }
}
