<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEntityToLitoExtensionFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lito_extension_files', function (Blueprint $table) {
            $table->string('entity')->nullable();
            $table->bigInteger('quote')->nullable();
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
            $table->dropColumn('entity');
            $table->dropColumn('quote');
        });
    }
}
