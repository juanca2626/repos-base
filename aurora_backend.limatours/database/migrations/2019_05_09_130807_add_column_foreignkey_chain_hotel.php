<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class AddColumnForeignkeyChainHotel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->unsignedBigInteger('chain_id')->after('max_age_teenagers');
            $table->foreign('chain_id')->references('id')->on('chains');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropForeign('hotels_chain_id_foreign');
            $table->dropColumn('chain_id');
        });
    }
}
