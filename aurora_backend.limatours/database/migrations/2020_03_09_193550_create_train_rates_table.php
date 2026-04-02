<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('train_template_id');
            $table->foreign('train_template_id')->references('id')->on('train_templates');
            $table->string('name');
            $table->boolean("status");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('train_rates');
    }
}
