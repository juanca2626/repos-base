<?php


use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
class AddPermissionFinancialExpensesMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'FinancialExpenses%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'FinancialExpenses: Create',
                    'slug' => 'financialexpenses.create',
                    'description' => 'Create new Gastos financieros', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'FinancialExpenses: Read',
                    'slug' => 'financialexpenses.read',
                    'description' => 'Read new Gastos financieros', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'FinancialExpenses: Update',
                    'slug' => 'financialexpenses.update',
                    'description' => 'Update new Gastos financieros', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'FinancialExpenses: Delete',
                    'slug' => 'financialexpenses.delete',
                    'description' => 'Delete new Gastos financieros', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'FinancialExpenses%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
