<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsOrderRatesServices extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'OrderRatesServices%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'OrderRatesServices: Create',
                    'slug' => 'orderratesservices.create',
                    'description' => 'Create new Orden Tarifario de servicios', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'OrderRatesServices: Read',
                    'slug' => 'orderratesservices.read',
                    'description' => 'Read new Orden Tarifario de servicios', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'OrderRatesServices: Update',
                    'slug' => 'orderratesservices.update',
                    'description' => 'Update new Orden Tarifario de servicios', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'OrderRatesServices: Delete',
                    'slug' => 'orderratesservices.delete',
                    'description' => 'Delete new Orden Tarifario de servicios', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'OrderRatesServices%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
