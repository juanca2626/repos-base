<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToReportHyperguests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_hyperguests', function (Blueprint $table) {
            $table->string('status')->default('PENDING')->after('fee');
            $table->integer('total_rows')->default(0)->after('status');
            $table->integer('processed_rows')->default(0)->after('total_rows');
            $table->text('error_message')->nullable()->after('processed_rows');
            $table->string('file_path')->nullable()->after('error_message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_hyperguests', function (Blueprint $table) {
            $table->dropColumn(['status', 'total_rows', 'processed_rows', 'error_message', 'file_path']);
        });
    }
}
