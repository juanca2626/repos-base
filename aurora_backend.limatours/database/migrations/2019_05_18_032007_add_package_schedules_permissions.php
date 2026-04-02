<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPackageSchedulesPermissions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageSchedules%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'PackageSchedules: Create',
                    'slug' => 'packageschedules.create',
                    'description' => 'Create new PackageSchedules', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageSchedules: Read',
                    'slug' => 'packageschedules.read',
                    'description' => 'Read new PackageSchedules', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageSchedules: Update',
                    'slug' => 'packageschedules.update',
                    'description' => 'Update new PackageSchedules', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageSchedules: Delete',
                    'slug' => 'packageschedules.delete',
                    'description' => 'Delete new PackageSchedules', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageSchedules%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
