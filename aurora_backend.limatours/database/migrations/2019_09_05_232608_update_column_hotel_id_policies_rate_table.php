<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnHotelIdPoliciesRateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policies_rates', function (Blueprint $table) {
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
        Schema::table('policies_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->nullable(false)->change();
        });
    }
}
