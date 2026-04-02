<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourcmsCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tourcms_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tourcms_header_id');
            $table->foreign('tourcms_header_id')->references('id')->on('tourcms_headers');
            $table->integer('customer_id');
            $table->text('customer_name')->nullable();
            $table->text('firstname')->nullable();
            $table->text('surname')->nullable();
            $table->text('customer_email')->nullable();
            $table->string('agecat_text', 45)->nullable();
            $table->char('agecat', 1)->nullable();
            $table->string('gender', 10)->nullable();
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
        Schema::dropIfExists('tourcms_customers');
    }
}
