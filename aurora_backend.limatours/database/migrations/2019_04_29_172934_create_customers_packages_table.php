<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('status', 1)->nullable();
            $table->double('markup')->nullable();
            $table->unsignedBigInteger('pack_id');
            $table->foreign('pack_id')->references('id')->on('packages');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('customers_packages');
    }
}
