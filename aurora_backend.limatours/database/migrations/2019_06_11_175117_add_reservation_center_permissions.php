<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddReservationCenterPermissions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ReservationCenter%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ReservationCenter: Create',
                    'slug' => 'reservationcenter.create',
                    'description' => 'Create new ReservationCenter', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenter: Read',
                    'slug' => 'reservationcenter.read',
                    'description' => 'Read new ReservationCenter', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenter: Update',
                    'slug' => 'reservationcenter.update',
                    'description' => 'Update new ReservationCenter', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenter: Delete',
                    'slug' => 'reservationcenter.delete',
                    'description' => 'Delete new ReservationCenter', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ReservationCenter%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
