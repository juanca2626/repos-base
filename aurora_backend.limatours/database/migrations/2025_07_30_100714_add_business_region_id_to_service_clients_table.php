<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBusinessRegionIdToServiceClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('business_region_id')->nullable()->after('period');

            $table->foreign('business_region_id')->references('id')->on('business_regions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_clients', function (Blueprint $table) {
            $table->dropColumn('business_region_id');
        });
    }
}
