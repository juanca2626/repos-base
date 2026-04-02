<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('day');
            $table->date('date');
            $table->integer('inventory_num');
            $table->integer('total_booking');
            $table->integer('total_cancelled');
            $table->smallInteger('locked');
            $table->unsignedBigInteger('package_id');
            $table->foreign('package_id')->references('id')->on('packages');
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
        Schema::dropIfExists('package_inventories');
    }
}
