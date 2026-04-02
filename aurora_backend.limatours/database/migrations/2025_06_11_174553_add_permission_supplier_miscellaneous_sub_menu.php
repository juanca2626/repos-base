<?php

use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionSupplierMiscellaneousSubMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplierMiscellaneous%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'SupplierMiscellaneous: Create',
                    'slug' => 'suppliermiscellaneous.create',
                    'description' => 'Create new supplier miscellaneous',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierMiscellaneous: Read',
                    'slug' => 'suppliermiscellaneous.read',
                    'description' => 'Read new supplier miscellaneous',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierMiscellaneous: Update',
                    'slug' => 'suppliermiscellaneous.update',
                    'description' => 'Update new supplier miscellaneous',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierMiscellaneous: Delete',
                    'slug' => 'suppliermiscellaneous.delete',
                    'description' => 'Delete new supplier miscellaneous',
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplierMiscellaneous%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
