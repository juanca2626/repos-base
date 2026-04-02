<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsClientPackagesOffer extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientPackageOffer%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientPackageOffer: Create',
                    'slug' => 'clientpackageoffer.create',
                    'description' => 'Create new Cliente - Paquetes ofertas', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientPackageOffer: Read',
                    'slug' => 'clientpackageoffer.read',
                    'description' => 'Read new Cliente - Paquetes ofertas', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientPackageOffer: Update',
                    'slug' => 'clientpackageoffer.update',
                    'description' => 'Update new Cliente - Paquetes ofertas', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientPackageOffer: Delete',
                    'slug' => 'clientpackageoffer.delete',
                    'description' => 'Delete new Cliente - Paquetes ofertas', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientPackageOffer%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }

}
