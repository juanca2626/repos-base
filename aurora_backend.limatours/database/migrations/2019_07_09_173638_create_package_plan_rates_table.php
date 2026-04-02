<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagePlanRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_plan_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('package_id');
            $table->foreign('package_id')->references('id')->on('packages');
            $table->string('name');
            $table->date('date_from');
            $table->date('date_to');
            $table->integer('age_child_from');
            $table->integer('age_child_to');
            $table->integer('age_infant_from');
            $table->integer('age_infant_to');
            $table->integer('pax_adult');
            $table->integer('pax_child');
            $table->integer('pax_infant');
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
        Schema::dropIfExists('package_plan_rates');
    }
}
