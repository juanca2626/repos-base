<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableGenericOtaServicesNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('extension_generic_ota_services', function (Blueprint $table) {
            $table->date('date_end')->after('date_start')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('extension_generic_ota_services', function (Blueprint $table) {
            $table->dropColumn('date_end');
        });
    }
}
