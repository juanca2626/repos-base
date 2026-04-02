<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnPoliciesServiceRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_rates', function (Blueprint $table) {
            $table->dropForeign(['service_policy_id']);
            $table->dropColumn('service_policy_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $policies = App\ServicePolicy::find(1);
        if( !( $policies ) ){
            $newP = new App\ServicePolicy();
            $newP->id = 1;
            $newP->save();
        }
        Schema::table('service_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('service_policy_id')->default(1);
            $table->foreign('service_policy_id')->references('id')->on('service_policies');
        });
    }
}
