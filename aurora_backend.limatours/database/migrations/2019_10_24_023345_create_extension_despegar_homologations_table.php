<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionDespegarHomologationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_despegar_homologations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('service_type',['X','T']);
            $table->string('description');
            $table->string('internal_code');
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
        Schema::dropIfExists('extension_despegar_homologations');
    }
}
