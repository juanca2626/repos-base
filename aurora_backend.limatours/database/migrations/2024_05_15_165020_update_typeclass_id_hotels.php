<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTypeclassIdHotels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE hotels CHANGE COLUMN `typeclass_id` `typeclass_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL;");
        DB::statement("ALTER TABLE hotels CHANGE COLUMN `preferential` `preferential` INT(2) UNSIGNED NULL DEFAULT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE hotels CHANGE COLUMN `typeclass_id` `typeclass_id` BIGINT(20) NOT NULL;");
        DB::statement("ALTER TABLE hotels CHANGE COLUMN `preferential` `preferential` INT(2) NOT NULL;");
    }
}
