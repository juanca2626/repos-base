<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsTourcmsReservationCenterModule extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ReservationCenterTourcms%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ReservationCenterTourcms: Create',
                    'slug' => 'reservationcentertourcms.create',
                    'description' => '', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenterTourcms: Read',
                    'slug' => 'reservationcentertourcms.read',
                    'description' => '', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenterTourcms: Update',
                    'slug' => 'reservationcentertourcms.update',
                    'description' => '', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenterTourcms: Delete',
                    'slug' => 'reservationcentertourcms.delete',
                    'description' => '', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ReservationCenterTourcms%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
