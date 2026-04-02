<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
class AddPermissionCostSaleAccountsMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'CostSaleAccounts%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'CostSaleAccounts: Create',
                    'slug' => 'costsaleaccounts.create',
                    'description' => 'Create new Cuentas costos & ventas', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'CostSaleAccounts: Read',
                    'slug' => 'costsaleaccounts.read',
                    'description' => 'Read new Cuentas costos & ventas', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'CostSaleAccounts: Update',
                    'slug' => 'costsaleaccounts.update',
                    'description' => 'Update new Cuentas costos & ventas', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'CostSaleAccounts: Delete',
                    'slug' => 'costsaleaccounts.delete',
                    'description' => 'Delete new Cuentas costos & ventas', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'CostSaleAccounts%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
