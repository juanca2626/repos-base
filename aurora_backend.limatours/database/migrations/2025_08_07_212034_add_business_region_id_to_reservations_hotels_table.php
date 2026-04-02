<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBusinessRegionIdToReservationsHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_hotels', function (Blueprint $table) {
            $table->unsignedBigInteger('business_region_id')->nullable()->after('chain_id');

            $table->foreign('business_region_id')
                  ->references('id')
                  ->on('business_regions')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_hotels', function (Blueprint $table) {
            $table->dropForeign(['business_region_id']);
            $table->dropColumn('business_region_id');
        });
    }
}
