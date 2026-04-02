<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

/**
 * Class AddPermissionsMarkupsConfigurationServices
 */
class AddPermissionsMarkupsConfigurationServices extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MarkupsConfigurationServices%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MarkupsConfigurationServices: Create',
                    'slug' => 'markupsconfigurationservices.create',
                    'description' => 'Create new Servicios - Configuracion de Markup de proteccion', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MarkupsConfigurationServices: Read',
                    'slug' => 'markupsconfigurationservices.read',
                    'description' => 'Read new Servicios - Configuracion de Markup de proteccion', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MarkupsConfigurationServices: Update',
                    'slug' => 'markupsconfigurationservices.update',
                    'description' => 'Update new Servicios - Configuracion de Markup de proteccion', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MarkupsConfigurationServices: Delete',
                    'slug' => 'markupsconfigurationservices.delete',
                    'description' => 'Delete new Servicios - Configuracion de Markup de proteccion', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MarkupsConfigurationServices%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
