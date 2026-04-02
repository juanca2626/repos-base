<?php

use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionSupplierStaffSubMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplierStaff%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'SupplierStaff: Create',
                    'slug' => 'supplierstaff.create',
                    'description' => 'Create new supplier staff',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierStaff: Read',
                    'slug' => 'supplierstaff.read',
                    'description' => 'Read new supplier staff',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierStaff: Update',
                    'slug' => 'supplierstaff.update',
                    'description' => 'Update new supplier staff',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierStaff: Delete',
                    'slug' => 'supplierstaff.delete',
                    'description' => 'Delete new supplier staff',
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplierStaff%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
