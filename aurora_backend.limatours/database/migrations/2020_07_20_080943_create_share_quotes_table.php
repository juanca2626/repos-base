<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShareQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('share_quotes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('view_permission')->default(true);
            $table->boolean('edit_permission')->default(false);
            $table->unsignedBigInteger('client_id');
            $table->foreign("client_id")->references('id')->on('clients');
            $table->unsignedBigInteger('quote_id');
            $table->foreign("quote_id")->references('id')->on('quotes');
            $table->unsignedBigInteger('user_id');
            $table->foreign("user_id")->references('id')->on('users');
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
        Schema::dropIfExists('share_quotes');
    }
}
