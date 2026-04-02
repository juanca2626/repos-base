<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsPackageChildren extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_children', function (Blueprint $table) {
            $table->smallInteger('percentage')->after('max_age')->default(0);
            $table->boolean('has_bed')->after('percentage')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_children', function (Blueprint $table) {
            $table->dropColumn(['percentage', 'has_bed']);
        });
    }
}
