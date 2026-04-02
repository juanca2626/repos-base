<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsTableClientServices extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientServices%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientServices: Create',
                    'slug' => 'clientservices.create',
                    'description' => 'Create new Cliente - Servicios', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientServices: Read',
                    'slug' => 'clientservices.read',
                    'description' => 'Read new Cliente - Servicios', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientServices: Update',
                    'slug' => 'clientservices.update',
                    'description' => 'Update new Cliente - Servicios', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientServices: Delete',
                    'slug' => 'clientservices.delete',
                    'description' => 'Delete new Cliente - Servicios', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientServices%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
