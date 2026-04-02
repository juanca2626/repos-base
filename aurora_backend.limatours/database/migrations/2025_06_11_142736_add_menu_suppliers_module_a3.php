<?php

use App\Models\Auth\App;
use App\Models\Auth\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMenuSuppliersModuleA3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $menuId = DB::table('menus')->insertGetId([
            'name' => 'Proveedores',
            'icon' => 'fa-user-tie',
            'app_id' => App::APP_NAME_NEGOTIATIONS,
            'target_site' => Menu::TARGET_SITE_A3,
        ]);
        
        $basePath = 'negotiations/suppliers';

        DB::table('sub_menus')->insert([
            [
                'name' => 'Transporte',
                'icon' => 'fa-bus',
                'slug' => 'suppliertransport',
                'target_site' => Menu::TARGET_SITE_A3,
                'path' => "{$basePath}/transports",
                'menu_id' => $menuId,
            ],
            [
                'name' => 'Atractivos turísticos',
                'icon' => 'fa-landmark',
                'slug' => 'suppliertouristattraction',
                'target_site' => Menu::TARGET_SITE_A3,
                'path' => "{$basePath}/tourist-attractions",
                'menu_id' => $menuId,
            ],
            [
                'name' => 'Restaurante',
                'icon' => 'fa-utensils',
                'slug' => 'supplierrestaurant',
                'target_site' => Menu::TARGET_SITE_A3,
                'path' => "{$basePath}/restaurants",
                'menu_id' => $menuId,
            ],
            [
                'name' => 'Operador turístico',
                'icon' => 'fa-person-hiking',
                'slug' => 'suppliertouroperator',
                'target_site' => Menu::TARGET_SITE_A3,
                'path' => "{$basePath}/tour-operators",
                'menu_id' => $menuId,
            ],
            [
                'name' => 'Staff',
                'icon' => 'fa-users',
                'slug' => 'supplierstaff',
                'target_site' => Menu::TARGET_SITE_A3,
                'path' => "{$basePath}/staff",
                'menu_id' => $menuId,
            ],
            [
                'name' => 'Misceláneos',
                'icon' => 'fa-cubes',
                'slug' => 'suppliermiscellaneous',
                'target_site' => Menu::TARGET_SITE_A3,
                'path' => "{$basePath}/miscellaneous",
                'menu_id' => $menuId,
            ]
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $menu = DB::table('menus')
            ->where('name', 'Proveedores')
            ->where('app_id', App::APP_NAME_NEGOTIATIONS)
            ->where('target_site', Menu::TARGET_SITE_A3)
            ->first();

        if ($menu)
        {
            DB::table('sub_menus')->where('menu_id', $menu->id)->delete();

            DB::table('menus')->where('id', $menu->id)->delete();
        }
    }

}
