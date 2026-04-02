<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHoraryPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horary_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pack_id');
            $table->foreign('pack_id')->references('id')->on('packages');
            $table->char('monday', 1)->nullable()->default('0');
            $table->char('tuesday', 1)->nullable();
            $table->char('wednesday', 1)->nullable()->default('0');
            $table->char('thursday', 1)->nullable()->default('0');
            $table->char('friday', 1)->nullable()->default('0');
            $table->char('saturday', 1)->nullable()->default('0');
            $table->char('sunday', 1)->nullable()->default('0');
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
        Schema::dropIfExists('horary_packages');
    }
}
