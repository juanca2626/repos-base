<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPackageTranslationsPermissions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageTranslations%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'PackageTranslations: Create',
                    'slug' => 'packagetranslations.create',
                    'description' => 'Create new PackageTranslations', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageTranslations: Read',
                    'slug' => 'packagetranslations.read',
                    'description' => 'Read new PackageTranslations', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageTranslations: Update',
                    'slug' => 'packagetranslations.update',
                    'description' => 'Update new PackageTranslations', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageTranslations: Delete',
                    'slug' => 'packagetranslations.delete',
                    'description' => 'Delete new PackageTranslations', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageTranslations%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
