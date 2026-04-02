<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionServiceTypesTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceTypes%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ServiceTypes: Create',
                    'slug' => 'servicetypes.create',
                    'description' => 'Create new ServiceTypes', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceTypes: Read',
                    'slug' => 'servicetypes.read',
                    'description' => 'Read new ServiceTypes', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceTypes: Update',
                    'slug' => 'servicetypes.update',
                    'description' => 'Update new ServiceTypes', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceTypes: Delete',
                    'slug' => 'servicetypes.delete',
                    'description' => 'Delete new ServiceTypes', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceTypes%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
