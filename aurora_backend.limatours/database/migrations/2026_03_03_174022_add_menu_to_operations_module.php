<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMenuToOperationsModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $idApp = DB::table('apps')->where('name', 'OPERACIONES')->first()->id;

        // MENUS..
        $menus = [
            [
                'name' => 'ManagementReports',
                'icon' => 'fa-tags',
                'slug' => 'opemanagementreports',
                'path' => 'ope/management-reports',
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
            'opemanagementreports',
        ];

        DB::table('menus')->whereIn('slug', $slugs)->delete();
    }
}
