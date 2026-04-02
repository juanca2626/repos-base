<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsTableClientServiceOffers extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientServiceOffer%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientServiceOffer: Create',
                    'slug' => 'clientserviceoffer.create',
                    'description' => 'Create new Cliente - Servicios ofertas', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientServiceOffer: Read',
                    'slug' => 'clientserviceoffer.read',
                    'description' => 'Read new Cliente - Servicios ofertas', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientServiceOffer: Update',
                    'slug' => 'clientserviceoffer.update',
                    'description' => 'Update new Cliente - Servicios ofertas', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientServiceOffer: Delete',
                    'slug' => 'clientserviceoffer.delete',
                    'description' => 'Delete new Cliente - Servicios ofertas', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientServiceOffer%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
