<?php

use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionCountryCalendarMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'CountryCalendar%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'CountryCalendar: Create',
                    'slug' => 'countrycalendar.create',
                    'description' => 'Create new country calendar',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'CountryCalendar: Read',
                    'slug' => 'countrycalendar.read',
                    'description' => 'Read new country calendar',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'CountryCalendar: Update',
                    'slug' => 'countrycalendar.update',
                    'description' => 'Update new country calendar',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'CountryCalendar: Delete',
                    'slug' => 'countrycalendar.delete',
                    'description' => 'Delete new country calendar',
                ]);
                $adminRole->attachPermission($permission);
                
                $permission = Permission::create([
                    'name' => 'CountryCalendar: Configuration',
                    'slug' => 'countrycalendar.configuration',
                    'description' => 'Configuration new country calendar',
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'CountryCalendar%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
