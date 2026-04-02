<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainChildrenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_children', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('train_template_id');
            $table->foreign('train_template_id')->references('id')->on('train_templates');
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->boolean('status');
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
        Schema::dropIfExists('train_children');
    }
}
