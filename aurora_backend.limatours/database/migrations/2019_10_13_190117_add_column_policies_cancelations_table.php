<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPoliciesCancelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policies_cancelations', function (Blueprint $table) {
            $table->integer('max_num')->after('hotel_id')->nullable();
            $table->integer('min_num')->after('hotel_id')->nullable();
            $table->integer('type_fit')->after('hotel_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('policies_cancelations', function (Blueprint $table) {
            $table->dropColumn('max_num');
            $table->dropColumn('min_num');
            $table->dropColumn('type_fit');
        });
    }
}
