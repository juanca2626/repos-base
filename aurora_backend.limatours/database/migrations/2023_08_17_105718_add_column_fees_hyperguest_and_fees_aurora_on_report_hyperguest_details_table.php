<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFeesHyperguestAndFeesAuroraOnReportHyperguestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_hyperguest_details', function (Blueprint $table) {
            $table->decimal('fees_hyperguest',10,2)->after('price_aurora');
            $table->decimal('fees_aurora',10,2)->after('price_aurora');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_hyperguests', function (Blueprint $table) {
            $table->dropColumn(['fees_hyperguest']);
            $table->dropColumn(['fees_aurora']);
        });
    }
}
