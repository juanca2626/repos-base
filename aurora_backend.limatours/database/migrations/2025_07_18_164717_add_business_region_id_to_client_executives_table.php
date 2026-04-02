<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBusinessRegionIdToClientExecutivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_executives', function (Blueprint $table) {
            $table->unsignedBigInteger('business_region_id')->after('id')->nullable();

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
        Schema::table('client_executives', function (Blueprint $table) {
            $table->dropForeign(['business_region_id']);
            $table->dropColumn('business_region_id');
        });
    }
}
