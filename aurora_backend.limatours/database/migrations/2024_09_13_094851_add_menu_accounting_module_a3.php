<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddMenuAccountingModuleA3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $idAppNegotiations = DB::table('apps')->insertGetId(
            ['name' => 'CONTABILIDAD'] //7
        );

        DB::table('menus')->insert([
            'name' => 'Configuración de IGV',
            'icon' => 'fa-gear',
            'slug' => 'taxgeneral',
            'path' => 'negotiations/accounting-management/tax/general',
            'app_id' => $idAppNegotiations,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Gastos financieros',
            'icon' => 'fa-file-invoice-dollar',
            'slug' => 'financialexpenses',
            'path' => 'negotiations/accounting-management/financial-expenses/general',
            'app_id' => $idAppNegotiations,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Tipo de cambio estimado',
            'icon' => 'fa-dollar-sign',
            'slug' => 'exchangerates',
            'path' => 'negotiations/accounting-management/exchange-rates/general',
            'app_id' => $idAppNegotiations,
            'target_site' => \App\Models\Auth\Menu::TARGET_SITE_A3,
        ]);

        DB::table('menus')->insert([
            'name' => 'Cuentas costos & ventas',
            'icon' => 'fa-receipt',
            'slug' => 'costsaleaccounts',
            'path' => 'negotiations/accounting-management/cost-sale-accounts/general',
            'app_id' => $idAppNegotiations,
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
        DB::table('menus')->where('slug', 'taxgeneral')->delete();
        DB::table('menus')->where('slug', 'financialexpenses')->delete();
        DB::table('menus')->where('slug', 'exchangerates')->delete();
        DB::table('menus')->where('slug', 'costsaleaccounts')->delete();
    }
}
