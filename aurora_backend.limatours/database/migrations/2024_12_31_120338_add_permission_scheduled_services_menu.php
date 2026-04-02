<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionScheduledServicesMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ScheduledServices%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ScheduledServices: Create',
                    'slug' => 'scheduledservices.create',
                    'description' => 'Create new Servicios programados', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ScheduledServices: Read',
                    'slug' => 'scheduledservices.read',
                    'description' => 'Read new Servicios programados', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ScheduledServices: Update',
                    'slug' => 'scheduledservices.update',
                    'description' => 'Update new Servicios programados', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ScheduledServices: Delete',
                    'slug' => 'scheduledservices.delete',
                    'description' => 'Delete new Servicios programados', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ScheduledServices%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
