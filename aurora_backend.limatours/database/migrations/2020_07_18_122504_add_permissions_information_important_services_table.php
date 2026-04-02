<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsInformationImportantServicesTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE',
                'InformationImportantServices%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'InformationImportantServices: Create',
                    'slug' => 'informationimportantservices.create',
                    'description' => 'Create new Info Important Service', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'InformationImportantServices: Read',
                    'slug' => 'informationimportantservices.read',
                    'description' => 'Read new Info Important Service', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'InformationImportantServices: Update',
                    'slug' => 'informationimportantservices.update',
                    'description' => 'Update Info Important Service', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'InformationImportantServices: Delete',
                    'slug' => 'informationimportantservices.delete',
                    'description' => 'Delete Info Important Service', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'InformationImportantServices%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
