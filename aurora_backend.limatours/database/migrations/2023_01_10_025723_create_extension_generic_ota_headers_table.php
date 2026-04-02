<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionGenericOtaHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_generic_ota_headers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('email');
            $table->dateTime('date_created');
            $table->tinyInteger('status');
            $table->unsignedBigInteger('ota_id');
            $table->timestamps();

            $table->foreign('ota_id')->references('id')->on('otas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_generic_ota_headers');
    }
}
