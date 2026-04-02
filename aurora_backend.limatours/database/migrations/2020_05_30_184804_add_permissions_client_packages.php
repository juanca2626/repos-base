<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsClientPackages extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientPackages%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientPackages: Create',
                    'slug' => 'clientpackages.create',
                    'description' => 'Create new Cliente - Paquetes', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientPackages: Read',
                    'slug' => 'clientpackages.read',
                    'description' => 'Read new Cliente - Paquetes', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientPackages: Update',
                    'slug' => 'clientpackages.update',
                    'description' => 'Update new Cliente - Paquetes', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientPackages: Delete',
                    'slug' => 'clientpackages.delete',
                    'description' => 'Delete new Cliente - Paquetes', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientPackages%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
