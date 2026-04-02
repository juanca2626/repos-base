<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsSupplementServices extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplementServices%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'SupplementServices: Create',
                    'slug' => 'supplementservices.create',
                    'description' => 'Create new Supplement Services', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplementServices: Read',
                    'slug' => 'supplementservices.read',
                    'description' => 'Read new Client Supplement Services', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplementServices: Update',
                    'slug' => 'supplementservices.update',
                    'description' => 'Update new Client Supplement Services', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'SupplementServices: Delete',
                    'slug' => 'supplementservices.delete',
                    'description' => 'Delete new Client Supplement Services', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'SupplementServices%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
