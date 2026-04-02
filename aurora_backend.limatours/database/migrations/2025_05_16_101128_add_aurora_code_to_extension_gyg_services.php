<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuroraCodeToExtensionGygServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('extension_gyg_services', function (Blueprint $table) {
            $table->string('aurora_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('extension_gyg_services', function (Blueprint $table) {
            $table->dropColumn('aurora_code');
        });
    }
}
