<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnServicePoliticsParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_politics_parameters', function (Blueprint $table) {
            $table->unsignedSmallInteger('unit_duration')->default(1)->after('max_hour')->comment('1 - horas; 2 - dias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_politics_parameters', function (Blueprint $table) {
            $table->dropColumn('unit_duration');
        });
    }
}
