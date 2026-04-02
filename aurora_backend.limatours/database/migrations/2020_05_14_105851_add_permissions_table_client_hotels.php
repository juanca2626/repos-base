<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsTableClientHotels extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientHotels%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientHotels: Create',
                    'slug' => 'clienthotels.create',
                    'description' => 'Create new Cliente - Hoteles', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientHotels: Read',
                    'slug' => 'clienthotels.read',
                    'description' => 'Read new Cliente - Hoteles', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientHotels: Update',
                    'slug' => 'clienthotels.update',
                    'description' => 'Update new Cliente - Hoteles', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientHotels: Delete',
                    'slug' => 'clienthotels.delete',
                    'description' => 'Delete new Cliente - Hoteles', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientHotels%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
