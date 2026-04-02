<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteColumnServiceCancellationPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        \App\ServicePoliticsParameter::truncate();
        \App\ServiceCancellationPolicies::truncate();
        Schema::enableForeignKeyConstraints();

        Schema::disableForeignKeyConstraints();
        Schema::table('service_cancellation_policies', function (Blueprint $table) {
            $table->dropForeign('service_cancellation_policies_service_id_foreign');
            $table->dropColumn('service_id');
        });
        Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
