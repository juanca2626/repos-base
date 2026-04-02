<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class AddForenkeyPolicyCancelationsIdToPolicyCancellationParameters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policy_cancellation_parameters', function (Blueprint $table) {
            $table->unsignedBigInteger('policy_cancelation_id');
            $table->foreign('policy_cancelation_id')->references('id')->on('policies_cancelations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('policy_cancellation_parameters', function (Blueprint $table) {
            $table->dropForeign('policies_cancelations_policy_cancelation_id_foreign');
            $table->dropColumn('policy_cancelation_id');
        });
    }
}
