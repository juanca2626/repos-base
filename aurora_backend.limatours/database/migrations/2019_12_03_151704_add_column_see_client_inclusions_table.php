<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSeeClientInclusionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_inclusions', function (Blueprint $table) {
            $table->unsignedSmallInteger('see_client')->default(1)->after('include')->comment('0 => no ve el cliente, 1=> si ve el cliente');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_inclusions', function (Blueprint $table) {
            $table->dropColumn('see_client');
        });
    }
}
