<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
class AddPermissionExchangeRatesMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ExchangeRates%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ExchangeRates: Create',
                    'slug' => 'exchangerates.create',
                    'description' => 'Create new Tipo de cambio estimado', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ExchangeRates: Read',
                    'slug' => 'exchangerates.read',
                    'description' => 'Read new Tipo de cambio estimado', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ExchangeRates: Update',
                    'slug' => 'exchangerates.update',
                    'description' => 'Update new Tipo de cambio estimado', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ExchangeRates: Delete',
                    'slug' => 'exchangerates.delete',
                    'description' => 'Delete new Tipo de cambio estimado', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ExchangeRates%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
