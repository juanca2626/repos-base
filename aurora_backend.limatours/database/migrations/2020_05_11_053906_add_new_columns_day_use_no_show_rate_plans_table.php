<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsDayUseNoShowRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rates_plans', function($table)
        {
            $table->text('no_show')->nullable();
            $table->text('day_use')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rates_plans', function($table)
        {
            $table->dropColumn('no_show');
            $table->dropColumn('day_use');
        });
    }
}
