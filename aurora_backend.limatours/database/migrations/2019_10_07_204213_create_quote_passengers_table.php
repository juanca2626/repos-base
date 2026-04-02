<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotePassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_passengers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender',['M','F'])->nullable();
            $table->date('birthday')->nullable();
            $table->string('document_number')->nullable();
            $table->string('doctype_iso')->nullable();
            $table->string('country_iso')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamps();


            $table->unsignedBigInteger('quote_id');
            $table->foreign('quote_id')->references('id')->on('quotes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_passengers');
    }
}
