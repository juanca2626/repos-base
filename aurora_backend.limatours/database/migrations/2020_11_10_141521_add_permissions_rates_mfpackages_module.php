<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsRatesMfpackagesModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $adminRole = Role::where('slug', '=', 'admin')->first();

        if ($adminRole !== null) {

            $permission = Permission::create([
                'name' => 'MenuFrontEnd_Packages: Rates',
                'slug' => 'mfpackages.rates',
                'description' => 'Read new Menu - Paquetes', // optional
            ]);
            $adminRole->attachPermission($permission);

        }

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
