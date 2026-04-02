<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToPackagePlanRatesAndInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_plan_rates', function (Blueprint $table) {
            $table->index(
                ['package_id', 'status', 'service_type_id', 'date_from', 'date_to'],
                'idx_ppr_package_status_service_dates'
            );
        });

        Schema::table('package_inventories', function (Blueprint $table) {
            $table->index(
                ['package_plan_rate_id', 'date'],
                'idx_pi_rate_date'
            );
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
            $table->dropIndex('idx_ppr_package_status_service_dates');
        });

        Schema::table('package_inventories', function (Blueprint $table) {
            $table->dropIndex('idx_pi_rate_date');
        });
    }
}
