<?php

use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionSupplierTransportSubMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplierTransport%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'SupplierTransport: Create',
                    'slug' => 'suppliertransport.create',
                    'description' => 'Create new supplier transport',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierTransport: Read',
                    'slug' => 'suppliertransport.read',
                    'description' => 'Read new supplier transport',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierTransport: Update',
                    'slug' => 'suppliertransport.update',
                    'description' => 'Update new supplier transport',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierTransport: Delete',
                    'slug' => 'suppliertransport.delete',
                    'description' => 'Delete new supplier transport',
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplierTransport%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
