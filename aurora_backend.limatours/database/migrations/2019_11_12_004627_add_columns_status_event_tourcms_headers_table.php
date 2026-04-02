<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsStatusEventTourcmsHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tourcms_headers', function (Blueprint $table) {
            $table->boolean('status_external')->after('status');
            $table->text('event')->after('customers_agecat_breakdown');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tourcms_headers', function (Blueprint $table) {
            $table->dropColumn(['status_external', 'event']);
        });
    }
}
