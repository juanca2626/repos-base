<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
class AddPermissionLockdownCalendarMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'LockdownCalendar%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'LockdownCalendar: Create',
                    'slug' => 'lockdowncalendar.create',
                    'description' => 'Create new Calendario de bloqueo', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'LockdownCalendar: Read',
                    'slug' => 'lockdowncalendar.read',
                    'description' => 'Read new Calendario de bloqueo', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'LockdownCalendar: Update',
                    'slug' => 'lockdowncalendar.update',
                    'description' => 'Update new Calendario de bloqueo', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'LockdownCalendar: Delete',
                    'slug' => 'lockdowncalendar.delete',
                    'description' => 'Delete new Calendario de bloqueo', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'LockdownCalendar%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
