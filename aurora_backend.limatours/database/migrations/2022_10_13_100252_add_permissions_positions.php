<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsPositions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Positions%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'Positions: Create',
                    'slug' => 'positions.create',
                    'description' => 'Create new Positions', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Positions: Read',
                    'slug' => 'positions.read',
                    'description' => 'Read new Positions', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Positions: Update',
                    'slug' => 'positions.update',
                    'description' => 'Update new Positions', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Positions: Delete',
                    'slug' => 'positions.delete',
                    'description' => 'Delete new Positions', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Positions%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
