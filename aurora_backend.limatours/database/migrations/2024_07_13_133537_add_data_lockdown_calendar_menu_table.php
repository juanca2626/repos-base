<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataLockdownCalendarMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('menus')->insert([
            'name' => 'Calendario de bloqueo',
            'icon' => 'fa-calendar',
            'slug' => 'lockdowncalendar',
            'path' => 'ope/lockdown-calendar',
            'app_id' => \App\Models\Auth\App::APP_NAME_OPERATIONS,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Pautas operativas',
            'icon' => 'fa-gear',
            'slug' => 'operationalguidelines',
            'path' => 'ope/operational-guidelines',
            'app_id' => \App\Models\Auth\App::APP_NAME_OPERATIONS,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('menus')->where('slug', 'lockdowncalendar')->delete();
        DB::table('menus')->where('slug', 'operationalguidelines')->delete();
    }
}
