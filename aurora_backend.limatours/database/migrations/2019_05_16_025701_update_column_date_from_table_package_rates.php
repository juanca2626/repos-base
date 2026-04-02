<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class UpdateColumnDateFromTablePackageRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE package_rates CHANGE COLUMN `date_from` `date_from` DATE NOT NULL ,
                             CHANGE COLUMN `date_to` `date_to` DATE NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE package_rates CHANGE COLUMN `date_from` `date_from` DATETIME NOT NULL ,
                             CHANGE COLUMN `date_to` `date_to` DATETIME NOT NULL");
    }
}
