<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBusinessRegionIdToHotelClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotel_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('business_region_id')->nullable()->after('id');
            $table->foreign('business_region_id')
                  ->references('id')
                  ->on('business_regions')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotel_clients', function (Blueprint $table) {
            $table->dropForeign(['business_region_id']);
            $table->dropColumn('business_region_id');
        });
    }
}
