<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsServiceTypeActivitiesTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceTypeActivities%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ServiceTypeActivities: Create',
                    'slug' => 'servicetypeactivities.create',
                    'description' => 'Create new Service Type Activities', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceTypeActivities: Read',
                    'slug' => 'servicetypeactivities.read',
                    'description' => 'Read new Service Type Activities', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceTypeActivities: Update',
                    'slug' => 'servicetypeactivities.update',
                    'description' => 'Update new Service Type Activities', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceTypeActivities: Delete',
                    'slug' => 'servicetypeactivities.delete',
                    'description' => 'Delete new Service Type Activities', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceTypeActivities%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
