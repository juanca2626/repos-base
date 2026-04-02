<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnClientRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_rate_plans', function (Blueprint $table) {
            if (Schema::hasColumn('client_rate_plans', 'business_region_id')) {
                $table->dropForeign(['business_region_id']);
                $table->dropColumn('business_region_id');
            }
        });
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
