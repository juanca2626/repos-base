<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
class AddPermissionTransportConfiguratorMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'TransportConfigurator%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'TransportConfigurator: Create',
                    'slug' => 'transportconfigurator.create',
                    'description' => 'Create new Configuración de tipo de unidades', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'TransportConfigurator: Read',
                    'slug' => 'transportconfigurator.read',
                    'description' => 'Read new Configuración de tipo de unidades', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'TransportConfigurator: Update',
                    'slug' => 'transportconfigurator.update',
                    'description' => 'Update new Configuración de tipo de unidades', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'TransportConfigurator: Delete',
                    'slug' => 'transportconfigurator.delete',
                    'description' => 'Delete new Configuración de tipo de unidades', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'TransportConfigurator%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
