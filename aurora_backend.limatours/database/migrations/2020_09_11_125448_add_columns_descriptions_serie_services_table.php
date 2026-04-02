<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsDescriptionsSerieServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serie_services', function (Blueprint $table) {
            $table->string('description_IT', 900)->after('status')->nullable();
            $table->string('description_PT', 900)->after('status')->nullable();
            $table->string('description_EN', 900)->after('status')->nullable();
            $table->string('description_ES', 900)->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serie_services', function (Blueprint $table) {
            $table->dropColumn(['description_ES', 'description_EN', 'description_PT', 'description_IT']);
        });
    }
}
