<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsOrderRatesHotels extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'OrderRatesHotels%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'OrderRatesHotels: Create',
                    'slug' => 'orderrateshotels.create',
                    'description' => 'Create new Orden Tarifario de Hoteles', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'OrderRatesHotels: Read',
                    'slug' => 'orderrateshotels.read',
                    'description' => 'Read new Orden Tarifario de Hoteles', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'OrderRatesHotels: Update',
                    'slug' => 'orderrateshotels.update',
                    'description' => 'Update new Orden Tarifario de Hoteles', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'OrderRatesHotels: Delete',
                    'slug' => 'orderrateshotels.delete',
                    'description' => 'Delete new Orden Tarifario de Hoteles', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'OrderRatesHotels%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
