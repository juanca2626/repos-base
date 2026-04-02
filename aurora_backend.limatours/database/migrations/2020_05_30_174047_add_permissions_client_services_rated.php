<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsClientServicesRated extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientServiceRated%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientServiceRated: Create',
                    'slug' => 'clientservicerated.create',
                    'description' => 'Create new Cliente - Servicios valoración', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientServiceRated: Read',
                    'slug' => 'clientservicerated.read',
                    'description' => 'Read new Cliente - Servicios valoración', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientServiceRated: Update',
                    'slug' => 'clientservicerated.update',
                    'description' => 'Update new Cliente - Servicios valoración', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientServiceRated: Delete',
                    'slug' => 'clientservicerated.delete',
                    'description' => 'Delete new Cliente - Servicios valoración', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientServiceRated%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
