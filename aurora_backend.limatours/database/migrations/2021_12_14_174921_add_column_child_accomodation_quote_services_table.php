<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnChildAccomodationQuoteServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_services', function (Blueprint $table) {
            $table->smallInteger('double_child')->default(0)->after('double');
            $table->smallInteger('triple_child')->default(0)->after('triple');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quote_services', function (Blueprint $table) {
            //
        });
    }
}
