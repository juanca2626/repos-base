<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMinMaxServiceCancellationPolicies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_cancellation_policies', function (Blueprint $table) {
            $table->integer('max_num')->after('name')->nullable();
            $table->integer('min_num')->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_cancellation_policies', function (Blueprint $table) {
            $table->dropColumn('max_num');
            $table->dropColumn('min_num');
        });
    }
}
