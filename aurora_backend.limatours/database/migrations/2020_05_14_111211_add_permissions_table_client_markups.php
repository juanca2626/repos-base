<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsTableClientMarkups extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientMarkups%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientMarkups: Create',
                    'slug' => 'clientmarkups.create',
                    'description' => 'Create new Cliente - Markups', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientMarkups: Read',
                    'slug' => 'clientmarkups.read',
                    'description' => 'Read new Cliente - Markups', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientMarkups: Update',
                    'slug' => 'clientmarkups.update',
                    'description' => 'Update new Cliente - Markups', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'ClientMarkups: Delete',
                    'slug' => 'clientmarkups.delete',
                    'description' => 'Delete new Cliente - Markups', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientMarkups%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
