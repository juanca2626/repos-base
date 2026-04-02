<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnHotelIdPoliciesCancelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policies_cancelations', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->nullable()->change();
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
            $table->unsignedBigInteger('hotel_id')->nullable(false)->change();
        });
    }
}
