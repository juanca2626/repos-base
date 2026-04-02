<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsDespegarReservationCenterModule extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ReservationCenterDespegar%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ReservationCenterDespegar: Create',
                    'slug' => 'reservationcenterdespegar.create',
                    'description' => '', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenterDespegar: Read',
                    'slug' => 'reservationcenterdespegar.read',
                    'description' => '', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenterDespegar: Update',
                    'slug' => 'reservationcenterdespegar.update',
                    'description' => '', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenterDespegar: Delete',
                    'slug' => 'reservationcenterdespegar.delete',
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ReservationCenterDespegar%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
