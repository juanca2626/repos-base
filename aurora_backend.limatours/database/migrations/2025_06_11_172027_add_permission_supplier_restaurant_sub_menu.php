<?php

use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionSupplierRestaurantSubMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplierRestaurant%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'SupplierRestaurant: Create',
                    'slug' => 'supplierrestaurant.create',
                    'description' => 'Create new supplier restaurant',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierRestaurant: Read',
                    'slug' => 'supplierrestaurant.read',
                    'description' => 'Read new supplier restaurant',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierRestaurant: Update',
                    'slug' => 'supplierrestaurant.update',
                    'description' => 'Update new supplier restaurant',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierRestaurant: Delete',
                    'slug' => 'supplierrestaurant.delete',
                    'description' => 'Delete new supplier restaurant',
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplierRestaurant%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
