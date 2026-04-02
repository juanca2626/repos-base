<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainInclusionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_inclusions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('see_client')->default(1)->comment('0 => no ve el cliente, 1=> si ve el cliente');
            $table->unsignedBigInteger('train_template_id')->unsigned()->index();
            $table->foreign('train_template_id')->references('id')->on('train_templates')->onDelete('cascade');
            $table->unsignedBigInteger('inclusion_id')->unsigned()->index();
            $table->foreign('inclusion_id')->references('id')->on('inclusions')->onDelete('cascade');
            $table->boolean('include');
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
        Schema::dropIfExists('train_inclusions');
    }
}
