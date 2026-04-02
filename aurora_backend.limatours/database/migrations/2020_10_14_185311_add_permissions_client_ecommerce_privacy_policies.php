<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsClientEcommercePrivacyPolicies extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientEcommercePrivacyPolicies%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientEcommercePrivacyPolicies: Create',
                    'slug' => 'clientecommerceprivacypolicies.create',
                    'description' => 'Create new Client Ecommerce - Privacy Policies', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ClientEcommercePrivacyPolicies: Read',
                    'slug' => 'clientecommerceprivacypolicies.read',
                    'description' => 'Read new Client Ecommerce - Privacy Policies', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ClientEcommercePrivacyPolicies: Update',
                    'slug' => 'clientecommerceprivacypolicies.update',
                    'description' => 'Update new Client Ecommerce - Privacy Policies', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ClientEcommercePrivacyPolicies: Delete',
                    'slug' => 'clientecommerceprivacypolicies.delete',
                    'description' => 'Delete new Client Ecommerce - Privacy Policies', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientEcommercePrivacyPolicies%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
