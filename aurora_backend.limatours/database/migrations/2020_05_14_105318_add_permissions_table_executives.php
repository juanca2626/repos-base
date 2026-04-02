<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsTableExecutives extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientExecutives%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientExecutives: Create',
                    'slug' => 'clientexecutives.create',
                    'description' => 'Create new Cliente - Ejecutivas', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientExecutives: Read',
                    'slug' => 'clientexecutives.read',
                    'description' => 'Read new Cliente - Ejecutivas', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientExecutives: Update',
                    'slug' => 'clientexecutives.update',
                    'description' => 'Update new Cliente - Ejecutivas', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientExecutives: Delete',
                    'slug' => 'clientexecutives.delete',
                    'description' => 'Delete new Cliente - Ejecutivas', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientExecutives%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
