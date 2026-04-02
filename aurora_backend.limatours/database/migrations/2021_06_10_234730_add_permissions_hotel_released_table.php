<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsHotelReleasedTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'hotelreleased%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'hotelreleased: Create',
                    'slug' => 'hotelreleased.create',
                    'description' => 'Create new Hotel - Liberados', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelreleased: Read',
                    'slug' => 'hotelreleased.read',
                    'description' => 'Read new Hotel - Liberados', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelreleased: Update',
                    'slug' => 'hotelreleased.update',
                    'description' => 'Update new Hotel - Liberados', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelreleased: Delete',
                    'slug' => 'hotelreleased.delete',
                    'description' => 'Delete new Hotel - Liberados', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'hotelreleased%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
