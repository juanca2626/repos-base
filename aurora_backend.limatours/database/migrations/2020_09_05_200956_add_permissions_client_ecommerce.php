<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsClientEcommerce extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientEcommerce%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientEcommerce: Create',
                    'slug' => 'clientecommerce.create',
                    'description' => 'Create new Client Ecommerce', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ClientEcommerce: Read',
                    'slug' => 'clientecommerce.read',
                    'description' => 'Read new Client Ecommerce', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ClientEcommerce: Update',
                    'slug' => 'clientecommerce.update',
                    'description' => 'Update new Client Ecommerce', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ClientEcommerce: Delete',
                    'slug' => 'clientecommerce.delete',
                    'description' => 'Delete new Client Ecommerce', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientEcommerce%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
