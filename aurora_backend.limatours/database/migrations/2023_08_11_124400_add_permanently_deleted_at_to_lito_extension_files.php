<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermanentlyDeletedAtToLitoExtensionFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lito_extension_files', function (Blueprint $table) {
            $table->datetime('permanently_deleted_at')->nullable();
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
            $table->dropColumn('permanently_deleted_at');
        });
    }
}
