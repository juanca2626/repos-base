<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImprovePackagePrioritiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('featured_packages', function (Blueprint $table) {
            $table->dropForeign(['pack_id']);
        });

        Schema::table('featured_packages', function (Blueprint $table) {
            $table->renameColumn('pack_id', 'package_id');
            $table->foreign('package_id')->references('id')->on('packages');
        });

        Schema::rename('featured_packages', 'package_priorities');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('package_priorities', 'featured_packages');

        Schema::table('featured_packages', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->renameColumn('package_id', 'pack_id');
        });

        Schema::table('featured_packages', function (Blueprint $table) {
            $table->foreign('pack_id')->references('id')->on('packages');;
        });
    }
}
