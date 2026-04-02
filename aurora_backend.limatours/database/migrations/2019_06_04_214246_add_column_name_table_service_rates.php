<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnNameTableServiceRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_rates', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->boolean('status')->nullable()->after('name');
            $table->boolean('promotions')->nullable()->after('name');
            $table->boolean('advance_sales')->nullable()->after('name');
            $table->boolean('services')->nullable()->after('name');
            $table->boolean('taxes')->nullable()->after('name');
            $table->boolean('allotment')->nullable()->after('name');
        });

        Schema::table('service_rates', function (Blueprint $table) {
            $table->dropForeign(['services_type_rate_id']);
        });

        Schema::table('service_rates', function (Blueprint $table) {
            $table->renameColumn('services_type_rate_id', 'service_type_rate_id');
            $table->foreign('service_type_rate_id')->references('id')->on('service_type_rates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_rates', function (Blueprint $table) {
            $table->dropColumn('name','status','promotions','advance_sales','services','taxes','allotment');
        });

        Schema::table('service_rates', function (Blueprint $table) {
            $table->dropForeign(['service_type_rate_id']);
            $table->renameColumn('service_type_rate_id', 'services_type_rate_id');
        });

        Schema::table('service_rates', function (Blueprint $table) {
            $table->foreign('services_type_rate_id')->references('id')->on('service_type_rates');
        });
    }
}
