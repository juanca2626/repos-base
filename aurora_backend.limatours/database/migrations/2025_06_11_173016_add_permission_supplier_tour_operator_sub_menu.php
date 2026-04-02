<?php

use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionSupplierTourOperatorSubMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplierTourOperator%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'SupplierTourOperator: Create',
                    'slug' => 'suppliertouroperator.create',
                    'description' => 'Create new supplier tour operator',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierTourOperator: Read',
                    'slug' => 'suppliertouroperator.read',
                    'description' => 'Read new supplier tour operator',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierTourOperator: Update',
                    'slug' => 'suppliertouroperator.update',
                    'description' => 'Update new supplier tour operator',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplierTourOperator: Delete',
                    'slug' => 'suppliertouroperator.delete',
                    'description' => 'Delete new supplier tour operator',
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplierTourOperator%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
