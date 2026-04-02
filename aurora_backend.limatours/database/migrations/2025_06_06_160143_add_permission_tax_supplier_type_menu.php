<?php

use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionTaxSupplierTypeMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'TaxSupplierType%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'TaxSupplierType: Create',
                    'slug' => 'taxsuppliertype.create',
                    'description' => 'Create new igv configuration by supplier type',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'TaxSupplierType: Read',
                    'slug' => 'taxsuppliertype.read',
                    'description' => 'Read new igv configuration by supplier type',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'TaxSupplierType: Update',
                    'slug' => 'taxsuppliertype.update',
                    'description' => 'Update new igv configuration by supplier type',
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'TaxSupplierType: Delete',
                    'slug' => 'taxsuppliertype.delete',
                    'description' => 'Delete new igv configuration by supplier type',
                ]);
                $adminRole->attachPermission($permission);
                
                $permission = Permission::create([
                    'name' => 'TaxSupplierType: Complete Assignment',
                    'slug' => 'taxsuppliertype.completeassignment',
                    'description' => 'Complete Assignment igv configuration by supplier type',
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'TaxSupplierType%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
