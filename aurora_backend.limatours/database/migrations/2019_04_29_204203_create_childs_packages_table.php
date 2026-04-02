<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChildsPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('childs_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pack_id');
            $table->foreign('pack_id')->references('id')->on('packages');
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
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
        Schema::dropIfExists('childs_packages');
    }
}
