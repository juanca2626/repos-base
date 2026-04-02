<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMenuAccountancySigModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $idAppSIG = DB::table('apps')->insertGetId(
            ['name' => 'SIG']
        );

        DB::table('menus')->insert([
            'name' => 'Producto No Conforme',
            'icon' => 'fa-book',
            'slug' => 'nonconformingproducts',
            'path' => 'accountancy/sig-tracking-platform/non-conforming-products',
            'app_id' => $idAppSIG,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Felicitación',
            'icon' => 'fa-heart',
            'slug' => 'congratulations',
            'path' => 'accountancy/sig-tracking-platform/congratulations',
            'app_id' => $idAppSIG,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Seguimiento de Gestión',
            'icon' => 'fa-th-list',
            'slug' => 'managementmonitoring',
            'path' => 'accountancy/sig-tracking-platform/management-monitoring',
            'app_id' => $idAppSIG,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Comentarios de Mejora',
            'icon' => 'fa-globe',
            'slug' => 'suggestionsforimprovement',
            'path' => 'accountancy/sig-tracking-platform/suggestions-for-improvement',
            'app_id' => $idAppSIG,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Mantenimiento de Sanciones',
            'icon' => 'fa-cog',
            'slug' => 'maintenanceofsanctions',
            'path' => 'accountancy/sig-tracking-platform/maintenance-of-sanctions',
            'app_id' => $idAppSIG,
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
        DB::table('menus')->where('slug', 'nonconformingproducts')->delete();
        DB::table('menus')->where('slug', 'congratulations')->delete();
        DB::table('menus')->where('slug', 'managementmonitoring')->delete();
        DB::table('menus')->where('slug', 'suggestionsforimprovement')->delete();
        DB::table('menus')->where('slug', 'maintenanceofsanctions')->delete();
    }
}
