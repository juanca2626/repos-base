<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsPaxsMasterSheetServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_sheet_services', function (Blueprint $table) {
            $table->integer('paxs')->after('comment')->nullable();
            $table->boolean('pax_status')->after('comment')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_sheet_services', function (Blueprint $table) {
            $table->dropColumn(['pax_status', 'paxs']);
        });
    }
}
