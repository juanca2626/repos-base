<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasiActivityJobLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masi_activity_job_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file',10)->nullable();
            $table->string('type_send',10)->default('email');
            $table->string('type_message',20)->nullable();
            $table->json('data')->nullable();
            $table->text('message')->nullable();
            $table->boolean('status_validation')->default(1);
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
        Schema::dropIfExists('masi_activity_job_logs');
    }
}
