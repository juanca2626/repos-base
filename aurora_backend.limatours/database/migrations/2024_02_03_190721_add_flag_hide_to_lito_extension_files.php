<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlagHideToLitoExtensionFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lito_extension_files', function (Blueprint $table) {
            $table->smallInteger('flag_hide')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lito_extension_files', function (Blueprint $table) {
            $table->dropColumn('flag_hide');
        });
    }
}
