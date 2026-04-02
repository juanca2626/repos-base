<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnServiceScheduleIdToServiceOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_operations', function (Blueprint $table) {
            $table->unsignedBigInteger('service_schedule_id')->nullable()->after('service_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_operations', function (Blueprint $table) {
            $table->removeColumn(['service_schedule_id']);
        });
    }
}
