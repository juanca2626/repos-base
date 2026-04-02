<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageServiceOptionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_service_optionals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['hotel', 'service']);
            $table->unsignedBigInteger('package_plan_rate_category_id');
            $table->foreign('package_plan_rate_category_id')->references('id')->on('package_plan_rate_categories');
            $table->bigInteger('object_id');
            $table->integer('order')->nullable();
            $table->date('date_in')->nullable();
            $table->date('date_out')->nullable();
            $table->integer('adult');
            $table->integer('child');
            $table->integer('infant');
            $table->integer('single')->nullable();
            $table->integer('double')->nullable();
            $table->integer('triple')->nullable();
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
        Schema::dropIfExists('package_service_optionals');
    }
}
