<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTypeCodeServiceSerieServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serie_services', function (Blueprint $table) {
            $table->integer('item_number')->after('code')->nullable();
            $table->string('type_code_service', 45)->after('code')->nullable();
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
            $table->dropColumn(['type_code_service', 'item_number']);
        });
    }
}
