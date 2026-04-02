<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPermissionsToChains extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Chains%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'Chains: Create',
                    'slug' => 'chains.create',
                    'description' => 'Create new Chains', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Chains: Read',
                    'slug' => 'chains.read',
                    'description' => 'Read new Chains', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Chains: Update',
                    'slug' => 'chains.update',
                    'description' => 'Update new Chains', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Chains: Delete',
                    'slug' => 'chains.delete',
                    'description' => 'Delete new Chains', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Chains%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
