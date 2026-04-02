<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_taxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('amount');
            $table->boolean('status')->default(false);
            $table->unsignedBigInteger('tax_id');
            $table->unsignedBigInteger('service_id');
            $table->foreign('tax_id')->references('id')->on('taxes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('service_id')->references('id')->on('services')->onUpdate('CASCADE')->onDelete('CASCADE');
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
        Schema::dropIfExists('service_taxes');
    }
}
