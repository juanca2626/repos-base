<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuroraContactUsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aurora_contact_us', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('name');
            $table->string('surname');
            $table->string('company');
            $table->string('email_from');
            $table->string('phone');
            $table->string('email_to');
            $table->string('subject');
            $table->text('message');
            $table->boolean('accept_privacy_policies')->default(true);
            $table->boolean('accept_data_processing')->default(true);
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
        Schema::dropIfExists('aurora_contactus');
    }
}
