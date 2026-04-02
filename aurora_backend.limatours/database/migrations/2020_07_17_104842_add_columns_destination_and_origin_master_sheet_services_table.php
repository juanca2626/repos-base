<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsDestinationAndOriginMasterSheetServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_sheet_services', function (Blueprint $table) {
            $table->string('destination_city', 5)->nullable()->after('check_out');
            $table->string('origin_city', 5)->nullable()->after('check_out');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_sheet_services', function (Blueprint $table) {
            $table->removeColumn(['destination_city', 'origin_city']);
        });
    }
}
