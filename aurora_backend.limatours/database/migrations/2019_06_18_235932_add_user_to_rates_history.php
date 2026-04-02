<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserToRatesHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rates_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('hotel_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rates_histories', function (Blueprint $table) {
            $table->dropForeign('rates_histories_user_id_foreign');
            $table->dropColumn('user_id');
        });
    }
}
