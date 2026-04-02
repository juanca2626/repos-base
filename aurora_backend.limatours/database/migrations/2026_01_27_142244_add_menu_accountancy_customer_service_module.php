<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMenuAccountancyCustomerServiceModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $idAppCustomerService = DB::table('apps')->insertGetId(
            ['name' => 'CUSTOMER SERVICE']
        );

        DB::table('menus')->insert([
            'name' => 'Reclamos',
            'icon' => 'fa-book',
            'slug' => 'claims',
            'path' => 'accountancy/customer-service/claims',
            'app_id' => $idAppCustomerService,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Reporte en Operación',
            'icon' => 'fa-book',
            'slug' => 'reports',
            'path' => 'accountancy/customer-service/reports',
            'app_id' => $idAppCustomerService,
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
        DB::table('menus')->where('slug', 'claims')->delete();
        DB::table('menus')->where('slug', 'reports')->delete();
    }
}
