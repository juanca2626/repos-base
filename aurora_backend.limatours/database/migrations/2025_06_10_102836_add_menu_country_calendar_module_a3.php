<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMenuCountryCalendarModuleA3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('menus')->insert([
            'name' => 'Calendarios de países',
            'icon' => 'fa-calendar-days',
            'slug' => 'countrycalendar',
            'path' => 'negotiations/country-calendar/general',
            'app_id' => \App\Models\Auth\App::APP_NAME_AUXILIARIES,
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
        DB::table('menus')
            ->where('slug', 'countrycalendar')
            ->delete();
    }
}
