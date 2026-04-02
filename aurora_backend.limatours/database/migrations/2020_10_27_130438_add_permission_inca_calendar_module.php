<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionIncaCalendarModule extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'IncaCalendar%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'IncaCalendar: Create',
                    'slug' => 'incacalendar.create',
                    'description' => 'Create new Inca Calendar', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'IncaCalendar: Read',
                    'slug' => 'incacalendar.read',
                    'description' => 'Read new Inca Calendar', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'IncaCalendar: Update',
                    'slug' => 'incacalendar.update',
                    'description' => 'Update new Inca Calendar', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'IncaCalendar: Delete',
                    'slug' => 'incacalendar.delete',
                    'description' => 'Delete new Inca Calendar', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
        }  //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $adminRole = Role::where('slug', '=', 'admin')->first();

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'IncaCalendar%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        } //
    }
}
