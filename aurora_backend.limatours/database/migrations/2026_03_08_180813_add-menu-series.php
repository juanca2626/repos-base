<?php

use Illuminate\Database\Migrations\Migration;

class AddMenuSeries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $idAppSeries = DB::table('apps')->insertGetId(
            ['name' => 'SERIES']
        );

        DB::table('menus')->insert([
            ['name' => 'Series Facile', 'app_id' => $idAppSeries, 'path' => 'series/series-programs', 'icon' => 'fa-boxes-stacked', 'slug' => 'seriesfacile', 'target_site' => 'a3'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('apps')->where('name', 'SERIES')->delete();
        DB::table('menus')->where('name', 'Serie Facile')->delete();
    }
}
