<?php

use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionTaxGeneralMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'TaxGeneral%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'TaxGeneral: Create',
                    'slug' => 'taxgeneral.create',
                    'description' => 'Create new Configuracion de Igv', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'TaxGeneral: Read',
                    'slug' => 'taxgeneral.read',
                    'description' => 'Read new Configuracion de Igv', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'TaxGeneral: Update',
                    'slug' => 'taxgeneral.update',
                    'description' => 'Update new Configuracion de Igv', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'TaxGeneral: Delete',
                    'slug' => 'taxgeneral.delete',
                    'description' => 'Delete new Configuracion de Igv', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $adminRole = Role::where('slug', '=', 'admin')->first();
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'TaxGeneral%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
