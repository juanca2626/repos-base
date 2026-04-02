<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInformationAditionalToLitoExtensionFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lito_extension_files', function (Blueprint $table) {
            $table->longText('information_aditional')->nullable();
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
            $table->dropColumn('information_aditional');
        });
    }
}
