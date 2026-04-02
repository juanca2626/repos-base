<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameLargeToLitoExtensionFileTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lito_extension_file_types', function (Blueprint $table) {
            $table->string('name_large')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lito_extension_file_types', function (Blueprint $table) {
            $table->dropColumn('name_large');
        });
    }
}
