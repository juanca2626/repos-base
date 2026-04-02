<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddDataOpeServiceManagementMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('menus')->insert([
            'name' => 'Programación de servicios',
            'icon' => 'fa-list-check',
            'slug' => 'servicescheduling',
            'path' => 'ope/service-management',
            'app_id' => \App\Models\Auth\App::APP_NAME_OPERATIONS,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Servicios programados',
            'icon' => 'fa-users',
            'slug' => 'scheduledservices',
            'path' => 'ope/providers',
            'app_id' => \App\Models\Auth\App::APP_NAME_OPERATIONS,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Torre de control',
            'icon' => 'fa-tower-observation',
            'slug' => 'controltower',
            'path' => 'ope/tower',
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
        DB::table('menus')->where('slug', 'servicemanagement')->delete();
        DB::table('menus')->where('slug', 'opeproviders')->delete();
        DB::table('menus')->where('slug', 'tower')->delete();
    }
}
