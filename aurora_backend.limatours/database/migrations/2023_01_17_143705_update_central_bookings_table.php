<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCentralBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('central_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('ota_id')->after('id')->nullable();

            $table->foreign('ota_id')->references('id')->on('otas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('central_bookings', function (Blueprint $table) {
            $table->dropColumn('ota_id');
        });
    }
}
