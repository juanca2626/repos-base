<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsSchedulesServiceRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_rate_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('service_penalty_id')->default(1)->after('user_id');
            $table->foreign('service_penalty_id')->references('id')->on('service_penalties');
            $table->boolean('sunday')->nullable()->after('date_to');
            $table->boolean('saturday')->nullable()->after('date_to');
            $table->boolean('friday')->nullable()->after('date_to');
            $table->boolean('thursday')->nullable()->after('date_to');
            $table->boolean('wednesday')->nullable()->after('date_to');
            $table->boolean('tuesday')->nullable()->after('date_to');
            $table->boolean('monday')->nullable()->after('date_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_rate_plans', function (Blueprint $table) {
            $table->dropForeign(['service_penalty_id']);
            $table->dropColumn(['service_penalty_id','sunday', 'saturday', 'friday', 'thursday', 'wednesday', 'tuesday', 'monday']);
        });
    }
}
