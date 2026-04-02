<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnForeignRatePlanRoomIdTablePackageServiceRooms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_service_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('rate_plan_room_id')->after('id');
            $table->foreign('rate_plan_room_id')->references('id')->on('rates_plans_rooms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('package_service_rooms', function (Blueprint $table) {
            $table->dropForeign('rate_plan_room_id');
            $table->dropColumn('rate_plan_room_id');
        });
    }
}
