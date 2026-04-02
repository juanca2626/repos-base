<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuoteDistributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_distributions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type_room');
            $table->string('type_room_name');
            $table->integer('occupation');
            $table->integer('single');
            $table->integer('double');
            $table->integer('triple');
            $table->integer('adult');
            $table->integer('child');            
            $table->integer('order'); 
            $table->unsignedBigInteger('quote_id'); 
            $table->timestamps();
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
        Schema::dropIfExists('quote_distributions');
    }
}
