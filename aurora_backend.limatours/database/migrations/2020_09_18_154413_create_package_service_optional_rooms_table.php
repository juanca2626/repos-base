<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageServiceOptionalRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_service_optional_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('package_service_optional_id')->unsigned();
            $table->foreign('package_service_optional_id','pso_id_foreign')->references('id')->on('package_service_optionals');
            $table->unsignedBigInteger('rate_plan_room_id');
            $table->foreign('rate_plan_room_id')->references('id')->on('rates_plans_rooms');
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
        Schema::dropIfExists('package_service_optional_rooms');
    }
}
