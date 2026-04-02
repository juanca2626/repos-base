<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPackageDestinationsPermissions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageDestinations%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'PackageDestinations: Create',
                    'slug' => 'packagedestinations.create',
                    'description' => 'Create new PackageDestinations', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageDestinations: Read',
                    'slug' => 'packagedestinations.read',
                    'description' => 'Read new PackageDestinations', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageDestinations: Update',
                    'slug' => 'packagedestinations.update',
                    'description' => 'Update new PackageDestinations', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageDestinations: Delete',
                    'slug' => 'packagedestinations.delete',
                    'description' => 'Delete new PackageDestinations', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageDestinations%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
