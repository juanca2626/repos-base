<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPackageChildrenPermissions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageChildren%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'PackageChildren: Create',
                    'slug' => 'packagechildren.create',
                    'description' => 'Create new PackageChildren', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageChildren: Read',
                    'slug' => 'packagechildren.read',
                    'description' => 'Read new PackageChildren', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageChildren: Update',
                    'slug' => 'packagechildren.update',
                    'description' => 'Update new PackageChildren', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageChildren: Delete',
                    'slug' => 'packagechildren.delete',
                    'description' => 'Delete new PackageChildren', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageChildren%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
