<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPackageCustomersPermissions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageCustomers%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'PackageCustomers: Create',
                    'slug' => 'packagecustomers.create',
                    'description' => 'Create new PackageCustomers', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageCustomers: Read',
                    'slug' => 'packagecustomers.read',
                    'description' => 'Read new PackageCustomers', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageCustomers: Update',
                    'slug' => 'packagecustomers.update',
                    'description' => 'Update new PackageCustomers', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageCustomers: Delete',
                    'slug' => 'packagecustomers.delete',
                    'description' => 'Delete new PackageCustomers', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageCustomers%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
