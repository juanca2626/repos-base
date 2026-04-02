<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnColorExperiencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('experiences', 'color')) {

            Schema::table('experiences', function (Blueprint $table) {
                $table->string('color', 8);
            });

            DB::statement("UPDATE experiences SET color='#EA882D' WHERE id=1 ");
            DB::statement("UPDATE experiences SET color='#85165F' WHERE id=2 ");
            DB::statement("UPDATE experiences SET color='#4BC910' WHERE id=3 ");
            DB::statement("UPDATE experiences SET color='#4A90E2' WHERE id=4 ");
            DB::statement("UPDATE experiences SET color='#04B5AA' WHERE id=5 ");
            DB::statement("UPDATE experiences SET color='#714C37' WHERE id=6 ");
            DB::statement("UPDATE experiences SET color='#DDCA1F' WHERE id=7 ");
            DB::statement("UPDATE experiences SET color='#CE3B4D' WHERE id=8 ");

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
