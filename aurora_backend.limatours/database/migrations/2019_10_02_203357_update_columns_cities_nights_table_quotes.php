<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnsCitiesNightsTableQuotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `quotes` 
            CHANGE COLUMN `cities` `cities` VARCHAR(191) NULL ,
            CHANGE COLUMN `nights` `nights` SMALLINT(6) NULL ;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `quotes` 
            CHANGE COLUMN `cities` `cities` VARCHAR(191) NOT NULL ,
            CHANGE COLUMN `nights` `nights` SMALLINT(6) NOT NULL ;");
    }
}
