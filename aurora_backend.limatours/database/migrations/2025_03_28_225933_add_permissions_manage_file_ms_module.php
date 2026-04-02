<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsManageFileMsModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $adminRole = Role::where('slug', '=', 'admin')->first();
        $especialistaRole = Role::where('slug', '=', 'ej')->first();
        $kamRole = Role::where('slug', '=', 'km')->first();
        $regionalesRole = Role::where('slug', '=', 'reg')->first();
        $regAdminRole = Role::where('slug', '=', 'radm')->first();
        //$operacionesRole = Role::where('slug', '=', 'op')->first();
        //$negRole = Role::where('slug', '=', 'neg')->first();

        if ($adminRole !== null) {

            $permission = Permission::create([
                'name' => 'Manage_Files_Ms: management',
                'slug' => 'manageFilesMs.management',
                'description' => 'Manage - Files Ms', 
            ]);

            $adminRole->attachPermission($permission);
            $especialistaRole->attachPermission($permission);
            $kamRole->attachPermission($permission);
            $regionalesRole->attachPermission($permission); 
            $regAdminRole->attachPermission($permission);
            
            $permission = Permission::create([
                'name' => 'Manage_Files_Ms: Module services composition',
                'slug' => 'manageFilesMs.module_services_composition',
                'description' => 'Manage - Files Ms', 
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
