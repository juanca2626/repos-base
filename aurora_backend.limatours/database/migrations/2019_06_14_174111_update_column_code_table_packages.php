<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnCodeTablePackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE packages CHANGE COLUMN code code CHAR(7) NULL,
                                DROP INDEX `code_UNIQUE`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE packages CHANGE COLUMN code code CHAR(7) NOT NULL,
                                ADD INDEX `code_UNIQUE`  (`code` ASC)");
    }
}
