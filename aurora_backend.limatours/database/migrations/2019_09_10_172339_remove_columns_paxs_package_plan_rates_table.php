<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnsPaxsPackagePlanRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_plan_rates', function (Blueprint $table) {
            $table->dropColumn('age_child_from');
            $table->dropColumn('age_child_to');
            $table->dropColumn('age_infant_from');
            $table->dropColumn('age_infant_to');
            $table->dropColumn('pax_adult');
            $table->dropColumn('pax_child');
            $table->dropColumn('pax_infant');
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
            $table->integer('pax_infant')->nullable()->after('date_to');
            $table->integer('pax_child')->nullable()->after('date_to');
            $table->integer('pax_adult')->nullable()->after('date_to');
            $table->integer('age_infant_from')->nullable()->after('date_to');
            $table->integer('age_child_to')->nullable()->after('date_to');
            $table->integer('age_child_from')->nullable()->after('date_to');
            $table->integer('age_infant_to')->nullable()->after('date_to');
        });
    }
}
