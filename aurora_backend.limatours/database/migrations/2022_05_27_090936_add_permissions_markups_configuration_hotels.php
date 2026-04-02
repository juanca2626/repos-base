<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsMarkupsConfigurationHotels extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MarkupsConfigurationHotels%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MarkupsConfigurationHotels: Create',
                    'slug' => 'markupsconfigurationhotels.create',
                    'description' => 'Create new Hoteles - Configuracion de Markup de proteccion', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MarkupsConfigurationHotels: Read',
                    'slug' => 'markupsconfigurationhotels.read',
                    'description' => 'Read new Hoteles - Configuracion de Markup de proteccion', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MarkupsConfigurationHotels: Update',
                    'slug' => 'markupsconfigurationhotels.update',
                    'description' => 'Update new Hoteles - Configuracion de Markup de proteccion', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MarkupsConfigurationHotels: Delete',
                    'slug' => 'markupsconfigurationhotels.delete',
                    'description' => 'Delete new Hoteles - Configuracion de Markup de proteccion', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MarkupsConfigurationHotels%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
