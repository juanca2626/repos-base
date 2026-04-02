<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnServiceTypeTablePackagePlanRate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_plan_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('service_type_id')->unsigned()->index()->default(1);
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_plan_rates', function (Blueprint $table) {
            $table->dropColumn('service_type_id')->unsigned()->index();
            $table->dropForeign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
        });
    }
}
