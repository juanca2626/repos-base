<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPackagePrioritiesPermissions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackagePriorities%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'PackagePriorities: Create',
                    'slug' => 'packagepriorities.create',
                    'description' => 'Create new PackagePriorities', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackagePriorities: Read',
                    'slug' => 'packagepriorities.read',
                    'description' => 'Read new PackagePriorities', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackagePriorities: Update',
                    'slug' => 'packagepriorities.update',
                    'description' => 'Update new PackagePriorities', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackagePriorities: Delete',
                    'slug' => 'packagepriorities.delete',
                    'description' => 'Delete new PackagePriorities', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackagePriorities%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
