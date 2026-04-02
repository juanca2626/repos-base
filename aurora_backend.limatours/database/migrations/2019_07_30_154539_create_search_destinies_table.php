<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSearchDestiniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_destinies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('index_search');
            $table->json('destiny');
            $table->json('date_range');
            $table->tinyInteger('quantity_rooms');
            $table->tinyInteger('quantity_adults');
            $table->tinyInteger('quantity_child');
            $table->json('quantity_persons_rooms');
            $table->string('typeclass_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_id');
            $table->uuid('token_search');
            $table->integer('quantity_hotels');
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
        Schema::dropIfExists('search_destinies');
    }
}
