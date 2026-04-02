<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class AddColumnsPolicyParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policy_cancellation_parameters', function (Blueprint $table) {
            $table->double('amount')->nullable();
            $table->boolean('tax');
            $table->boolean('service');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('policy_cancellation_parameters', function (Blueprint $table) {
            $table->dropColumn(['amount', 'tax', 'service']);
        });
    }
}
