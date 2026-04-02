<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdatePathForTaxGeneralInMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('menus')
            ->where('slug', 'taxgeneral')
            ->where('path', 'negotiations/accounting-management/tax/general')
            ->update(['path' => 'negotiations/accounting-management/tax']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
