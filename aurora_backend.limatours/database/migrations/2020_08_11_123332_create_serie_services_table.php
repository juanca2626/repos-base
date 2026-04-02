<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerieServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serie_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('serie_category_id');
            $table->foreign('serie_category_id')->references('id')->on('serie_categories');
            $table->string('code', 45)->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->date('date')->nullable();
            $table->tinyInteger('duration')->nullable();
            $table->string('type_service', 45);
            $table->tinyInteger('status');
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
        Schema::dropIfExists('serie_services');
    }
}
