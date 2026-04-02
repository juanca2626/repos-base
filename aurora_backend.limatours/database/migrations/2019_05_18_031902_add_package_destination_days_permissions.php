<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPackageDestinationDaysPermissions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageDestinationDays%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'PackageDestinationDays: Create',
                    'slug' => 'packagedestinationdays.create',
                    'description' => 'Create new PackageDestinationDays', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageDestinationDays: Read',
                    'slug' => 'packagedestinationdays.read',
                    'description' => 'Read new PackageDestinationDays', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageDestinationDays: Update',
                    'slug' => 'packagedestinationdays.update',
                    'description' => 'Update new PackageDestinationDays', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageDestinationDays: Delete',
                    'slug' => 'packagedestinationdays.delete',
                    'description' => 'Delete new PackageDestinationDays', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageDestinationDays%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
