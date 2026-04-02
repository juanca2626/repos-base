<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsClientPackagesRated extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientPackageRated%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientPackageRated: Create',
                    'slug' => 'clientpackagerated.create',
                    'description' => 'Create new Cliente - Paquetes valoración', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientPackageRated: Read',
                    'slug' => 'clientpackagerated.read',
                    'description' => 'Read new Cliente - Paquetes valoración', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientPackageRated: Update',
                    'slug' => 'clientpackagerated.update',
                    'description' => 'Update new Cliente - Paquetes valoración', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientPackageRated: Delete',
                    'slug' => 'clientpackagerated.delete',
                    'description' => 'Delete new Cliente - Paquetes valoración', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientPackageRated%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
