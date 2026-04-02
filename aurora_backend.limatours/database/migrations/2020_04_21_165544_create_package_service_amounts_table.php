<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageServiceAmountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_service_amounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('amount',8,2);
            $table->unsignedBigInteger('package_service_id');
            $table->foreign('package_service_id')->references('id')->on('package_services');
            $table->float('single',8,4)->default("0.0000");
            $table->float('double',8,4)->default("0.0000");
            $table->float('triple',8,4)->default("0.0000");
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
        Schema::dropIfExists('package_service_amounts');
    }
}
