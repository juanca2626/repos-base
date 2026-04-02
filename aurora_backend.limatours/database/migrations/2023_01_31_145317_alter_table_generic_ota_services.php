<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableGenericOtaServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('extension_generic_ota_services', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->default(null);

            $table->string('multi_days',1)->nullable()->default(null);

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
            $table->dropColumn('multi_days');
            $table->dropForeign('package_id');
            $table->dropColumn('package_id');
        });
    }
}
