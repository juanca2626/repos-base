<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceOperationActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_operation_activities', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('service_type_activity_id');
            $table->foreign('service_type_activity_id')->references('id')->on('service_type_activities')->onDelete('cascade');

            $table->unsignedBigInteger('service_operation_id');
            $table->foreign('service_operation_id')->references('id')->on('service_operations');

            $table->integer('minutes')->nullable();
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
        Schema::dropIfExists('service_operation_activities');
    }
}
