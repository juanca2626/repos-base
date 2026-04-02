<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnTableClientRatePlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_rate_plans', function (Blueprint $table) {
            $table->dropColumn('num_days_blocked');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_rate_plans', function (Blueprint $table) {
            $table->tinyInteger('num_days_blocked')->nullable();
        });
    }
}
