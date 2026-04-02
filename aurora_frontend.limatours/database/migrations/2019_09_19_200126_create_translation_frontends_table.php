<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslationFrontendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translation_frontends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug');
            $table->string('value')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('module_id');
            $table->foreign('module_id')->references('id')->on('modules');

            $table->unsignedBigInteger('language_id');
            $table->foreign('language_id')->references('id')->on('languages');

            $table->unique(['slug', 'module_id','language_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translation_frontends');
    }
}
