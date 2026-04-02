<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsBookingsLogs extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Bookings%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'Bookings: Create',
                    'slug' => 'bookings.create',
                    'description' => 'Create new Bookings', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Bookings: Read',
                    'slug' => 'bookings.read',
                    'description' => 'Read new Bookings', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Bookings: Update',
                    'slug' => 'bookings.update',
                    'description' => 'Update new Bookings', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Bookings: Delete',
                    'slug' => 'bookings.delete',
                    'description' => 'Delete new Bookings', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Bookings%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
