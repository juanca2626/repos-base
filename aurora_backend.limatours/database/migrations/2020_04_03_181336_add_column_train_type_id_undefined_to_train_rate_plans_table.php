<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTrainTypeIdUndefinedToTrainRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('train_rate_plans', function (Blueprint $table) {
            $table->tinyInteger('train_type_id_undefined')->after('train_type_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('train_rate_plans', function (Blueprint $table) {
            $table->dropColumn('train_type_id_undefined');
        });
    }
}
