<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobStatusListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_status_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('entity');
            $table->unsignedBigInteger('object_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('email_notification')->nullable();
            $table->string('year')->nullable();
            $table->text('data')->nullable();
            $table->text('error_message')->nullable();
            $table->string('job_status')->default(0);
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
        Schema::dropIfExists('job_status_lists');
    }
}
