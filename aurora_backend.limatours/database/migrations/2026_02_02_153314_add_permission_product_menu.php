<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionProductMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $idAppNEG = DB::table('apps')->insertGetId(
            ['name' => 'NEGOCIACIONES']
        );

        DB::table('menus')->insert([
            'name' => 'Producto',
            'icon' => 'fa-book',
            'slug' => 'products',
            'path' => 'negotiations/products/general',
            'app_id' => $idAppNEG,
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
        DB::table('menus')->where('slug', 'products')->delete();

    }
}
