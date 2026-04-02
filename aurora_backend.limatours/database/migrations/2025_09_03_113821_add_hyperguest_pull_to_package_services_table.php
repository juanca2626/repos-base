<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHyperguestPullToPackageServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_services', function (Blueprint $table) {
            $table->tinyInteger('hyperguest_pull')->default(0)->after('destiny');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_services', function (Blueprint $table) {
            $table->dropColumn('hyperguest_pull');
        });
    }
}
