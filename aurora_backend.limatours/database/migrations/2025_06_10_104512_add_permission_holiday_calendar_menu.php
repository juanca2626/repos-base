<?php

use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionHolidayCalendarMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'HolidayCalendar%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'HolidayCalendar: Create',
                    'slug' => 'holidaycalendar.create',
                    'description' => 'Create new holiday calendar',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'HolidayCalendar: Read',
                    'slug' => 'holidaycalendar.read',
                    'description' => 'Read new holiday calendar',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'HolidayCalendar: Update',
                    'slug' => 'holidaycalendar.update',
                    'description' => 'Update new holiday calendar',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'HolidayCalendar: Delete',
                    'slug' => 'holidaycalendar.delete',
                    'description' => 'Delete new holiday calendar',
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'HolidayCalendar%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
