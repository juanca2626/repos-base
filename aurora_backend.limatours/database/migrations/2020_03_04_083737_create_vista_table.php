<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVistaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vista', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('direction')->nullable();
            $table->text('logo_url')->nullable();
            $table->enum(
                'type_banner_main',
                [
                    'images_upload',
                    'images_url',
                    'video_url',
                ]
            )->nullable();
            $table->text('facebook')->nullable();
            $table->text('instagram')->nullable();
            $table->text('twitter')->nullable();
            $table->string('whatsapp')->nullable();
            $table->boolean('main')->default(false)->comment('Determina que web es la principal');
            $table->boolean('status')->default(false);
            $table->foreign('client_id')->references('id')->on('clients');
            $table->index(['slug','name']);
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
        Schema::dropIfExists('vista');
    }
}
