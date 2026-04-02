<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsPackagePermissionsTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackagePermissions%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'PackagePermissions: Create',
                    'slug' => 'packagepermissions.create',
                    'description' => 'Create new Package Permissions', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackagePermissions: Read',
                    'slug' => 'packagepermissions.read',
                    'description' => 'Read new Package Permissions', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackagePermissions: Update',
                    'slug' => 'packagepermissions.update',
                    'description' => 'Update new Package Permissions', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackagePermissions: Delete',
                    'slug' => 'packagepermissions.delete',
                    'description' => 'Delete new Package Permissions', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackagePermissions%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
