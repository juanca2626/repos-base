<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableColumnsPassengersQuote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_passengers', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `quote_passengers` CHANGE COLUMN `first_name` `first_name` VARCHAR(191) NULL ;");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `quote_passengers` CHANGE COLUMN `last_name` `last_name` VARCHAR(191) NULL ;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quote_passengers', function (Blueprint $table) {
            //
        });
    }
}
