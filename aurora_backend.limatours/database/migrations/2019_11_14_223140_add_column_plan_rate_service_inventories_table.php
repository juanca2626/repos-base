<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnPlanRateServiceInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('service_inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('service_rate_id')->after('service_id');
            $table->foreign('service_rate_id')->references('id')->on('service_rates');
        });
        Schema::EnableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('service_inventories', function (Blueprint $table) {
            $table->dropForeign('service_inventories_service_rate_id_foreign');
            $table->dropColumn('service_rate_id');
        });
        Schema::EnableForeignKeyConstraints();
    }
}
