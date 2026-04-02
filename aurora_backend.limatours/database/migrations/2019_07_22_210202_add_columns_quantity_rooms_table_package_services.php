<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsQuantityRoomsTablePackageServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_services', function (Blueprint $table) {
            $table->integer('triple')->nullable()->after('infant');
            $table->integer('double')->nullable()->after('infant');
            $table->integer('single')->nullable()->after('infant');
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
            $table->dropColumn('single');
            $table->dropColumn('double');
            $table->dropColumn('triple');
        });
    }
}
