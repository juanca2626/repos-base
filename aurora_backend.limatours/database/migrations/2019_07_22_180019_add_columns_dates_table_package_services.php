<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsDatesTablePackageServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_services', function (Blueprint $table) {
            $table->date('date_out')->nullable()->after('order');
            $table->date('date_in')->nullable()->after('order');
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
            $table->dropColumn('date_out');
            $table->dropColumn('date_in');
        });
    }
}
