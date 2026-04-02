<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarkupTrainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('markup_trains', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('period');
            $table->decimal('markup', 10, 2);
            $table->unsignedBigInteger('train_template_id')->unsigned()->index();
            $table->unsignedBigInteger('client_id')->unsigned()->index();
            $table->foreign('train_template_id')->references('id')->on('train_templates');
            $table->foreign('client_id')->references('id')->on('clients');
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
        Schema::dropIfExists('markup_trains');
    }
}
