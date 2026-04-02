<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMultimediaPhotoFilters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multimedia_photo_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tag');
            $table->unsignedBigInteger('multimedia_id');
            $table->foreign('multimedia_id')->references('id')->on('multimedia');
            $table->boolean('status')->default(1)->comment('0 => inactivo; 1 => activo');
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
        Schema::dropIfExists('multimedia_photo_filters');
    }
}
