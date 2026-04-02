<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMenuAdventureModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $idApp = DB::table('apps')->insertGetId(
            ['name' => 'ADVENTURE']
        );

        // MENUS..
        $menus = [
            [
                'name' => 'ACategories',
                'icon' => 'fa-tags',
                'slug' => 'acategories',
                'path' => 'adventure/categories',
            ],
            [
                'name' => 'ASettings',
                'icon' => 'fa-cog',
                'slug' => 'asettings',
                'path' => 'adventure/configuration',
            ],
            [
                'name' => 'Templates',
                'icon' => 'fa-copy',
                'slug' => 'templates',
                'path' => 'adventure/templates',
            ],
            [
                'name' => 'Departures',
                'icon' => 'fa-calendar',
                'slug' => 'departures',
                'path' => 'adventure/departures',
            ],
            [
                'name' => 'Entrances',
                'icon' => 'fa-ticket-alt',
                'slug' => 'entrances',
                'path' => 'adventure/entrances',
            ],
            [
                'name' => 'Cash',
                'icon' => 'fa-money-bill',
                'slug' => 'cash',
                'path' => 'adventure/cash',
            ],
            [
                'name' => 'Programming',
                'icon' => 'fa-list',
                'slug' => 'programming',
                'path' => 'adventure/programming',
            ],
            [
                'name' => 'Manifest',
                'icon' => 'fa-user-tie',
                'slug' => 'manifest',
                'path' => 'adventure/manifest',
            ],
            [
                'name' => 'ACalendar',
                'icon' => 'fa-calendar-alt',
                'slug' => 'acalendar',
                'path' => 'adventure/calendar',
            ],
            [
                'name' => 'AServices',
                'icon' => 'fa-plus',
                'slug' => 'aservices',
                'path' => 'adventure/services',
            ],
        ];

        foreach ($menus as $menu) {
            DB::table('menus')->insert([
                'name' => $menu['name'],
                'icon' => $menu['icon'],
                'slug' => $menu['slug'],
                'path' => $menu['path'],
                'app_id' => $idApp,
                'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $slugs = [
            'acategories',
            'asettings',
            'templates',
            'departures',
            'tickets',
            'cash',
            'programming',
            'manifest',
            'acalendar',
            'aservices',
        ];

        DB::table('menus')->whereIn('slug', $slugs)->delete();
        DB::table('apps')->where('name', 'Aventura')->delete();
    }
}
