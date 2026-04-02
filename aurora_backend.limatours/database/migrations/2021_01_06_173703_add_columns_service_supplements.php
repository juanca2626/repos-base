<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsServiceSupplements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_supplements', function (Blueprint $table) {
            $table->json('days_to_charge')->nullable()->after('type');
            $table->boolean('charge_all_pax')->default(0)->after('days_to_charge');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_supplements', function (Blueprint $table) {
            $table->dropColumn('days_to_charge');
            $table->dropColumn('charge_all_pax');
        });
    }
}
