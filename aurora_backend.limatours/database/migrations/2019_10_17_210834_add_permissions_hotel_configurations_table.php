<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsHotelConfigurationsTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'hotelconfigurations%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'hotelconfigurations: Create',
                    'slug' => 'hotelconfigurations.create',
                    'description' => 'Create new hotelconfigurations', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelconfigurations: Read',
                    'slug' => 'hotelconfigurations.read',
                    'description' => 'Read new hotelconfigurations', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelconfigurations: Update',
                    'slug' => 'hotelconfigurations.update',
                    'description' => 'Update new hotelconfigurations', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelconfigurations: Delete',
                    'slug' => 'hotelconfigurations.delete',
                    'description' => 'Delete new hotelconfigurations', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'hotelconfigurations%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
