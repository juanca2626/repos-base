<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImprovePackageImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('images_packages', function (Blueprint $table) {
            $table->dropForeign(['pack_id']);
        });

        Schema::table('images_packages', function (Blueprint $table) {
            $table->renameColumn('pack_id', 'package_id');
            $table->foreign('package_id')->references('id')->on('packages');
        });

        Schema::rename('images_packages', 'package_images');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('package_images', 'images_packages');

        Schema::table('images_packages', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->renameColumn('package_id', 'pack_id');
        });

        Schema::table('images_packages', function (Blueprint $table) {
            $table->foreign('pack_id')->references('id')->on('packages');;
        });
    }
}
