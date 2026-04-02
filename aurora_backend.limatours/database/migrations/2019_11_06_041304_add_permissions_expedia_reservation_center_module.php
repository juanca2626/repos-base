<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsExpediaReservationCenterModule extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ReservationCenterExpedia%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ReservationCenterExpedia: Create',
                    'slug' => 'reservationcenterexpedia.create',
                    'description' => '', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenterExpedia: Read',
                    'slug' => 'reservationcenterexpedia.read',
                    'description' => '', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenterExpedia: Update',
                    'slug' => 'reservationcenterexpedia.update',
                    'description' => '', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ReservationCenterExpedia: Delete',
                    'slug' => 'reservationcenterexpedia.delete',
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ReservationCenterExpedia%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
