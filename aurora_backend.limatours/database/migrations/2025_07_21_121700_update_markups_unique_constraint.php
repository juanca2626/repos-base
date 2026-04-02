<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMarkupsUniqueConstraint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('markups', function (Blueprint $table) {
            $table->dropUnique(['period', 'client_id']);
            $table->unique(['period', 'client_id', 'business_region_id'], 'markups_period_client_region_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('markups', function (Blueprint $table) {
            $table->dropUnique('markups_period_client_region_unique');
            $table->unique(['period', 'client_id'], 'markups_period_client_id_unique');
        });
    }
}
