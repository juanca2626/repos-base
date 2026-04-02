<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToPackagesRelatedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_itineraries', function (Blueprint $table) {
            $table->index(
                ['package_id', 'language_id', 'year'],
                'idx_pi_package_lang_year'
            );
        });

        Schema::table('client_package_rated', function (Blueprint $table) {
            $table->index(
                ['client_id', 'package_id'],
                'idx_cpr_client_package'
            );
        });

        Schema::table('client_package_settings', function (Blueprint $table) {
            $table->index(
                ['client_id', 'package_id'],
                'idx_cps_client_package'
            );
        });

        Schema::table('client_package_offers', function (Blueprint $table) {
            $table->index(
                ['client_id', 'package_plan_rate_id', 'date_from', 'date_to'],
                'idx_cpo_client_rate_dates'
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
        Schema::table('package_itineraries', function (Blueprint $table) {
            $table->dropIndex('idx_pi_package_lang_year');
        });

        Schema::table('client_package_rated', function (Blueprint $table) {
            $table->dropIndex('idx_cpr_client_package');
        });

        Schema::table('client_package_settings', function (Blueprint $table) {
            $table->dropIndex('idx_cps_client_package');
        });

        Schema::table('client_package_offers', function (Blueprint $table) {
            $table->dropIndex('idx_cpo_client_rate_dates');
        });
    }
}
