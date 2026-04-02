<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddMenuSettingUnitTypeModuleA3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('menus')->insert([
            'name' => 'Configuración de tipo de unidades',
            'icon' => 'fa-bus',
            'slug' => 'transportconfigurator',
            'path' => 'negotiations/transport-configurator/general',
            'app_id' => \App\Models\Auth\App::APP_NAME_NEGOTIATIONS,
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
        DB::table('menus')->where('slug', 'transportconfigurator')->delete();
    }
}
