<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMethodNameChannelsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels_logs', function (Blueprint $table) {
            $table->string('method_name', 100)->nullable()->after('response_headers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('channels_logs', function (Blueprint $table) {
            $table->dropColumn('method_name');
        });
    }
}
