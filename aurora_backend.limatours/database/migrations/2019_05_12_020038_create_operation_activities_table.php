<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operation_activities', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('service_type_activity_id');
            $table->foreign('service_type_activity_id')->references('id')->on('service_type_activities')->onDelete('cascade');

            $table->unsignedBigInteger('service_operation_id');
            $table->foreign('service_operation_id')->references('id')->on('service_operations')->onDelete('cascade');

            $table->time('time');
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
        Schema::dropIfExists('operation_activities');
    }
}
