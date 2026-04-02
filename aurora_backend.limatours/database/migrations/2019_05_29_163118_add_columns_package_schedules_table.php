<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsPackageSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_schedules', function (Blueprint $table) {
            $table->date('date_to')->nullable()->after('package_id');
            $table->date('date_from')->nullable()->after('package_id');
            $table->boolean('state')->nullable()->after('sunday');
            $table->integer('room')->nullable()->after('sunday');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_schedules', function (Blueprint $table) {
            $table->dropColumn(['date_from', 'date_to', 'state', 'room']);
        });
    }
}
