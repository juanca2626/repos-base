<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class DeleteColumnsCheckServiceAndTexToPoliciesCancelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policies_cancelations', function (Blueprint $table) {
            $table->dropColumn('check_taxe');
            $table->dropColumn('check_service');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('policies_cancelations', function (Blueprint $table) {
            $table->tinyInteger('check_taxe');
            $table->tinyInteger('check_service');
        });
    }
}
