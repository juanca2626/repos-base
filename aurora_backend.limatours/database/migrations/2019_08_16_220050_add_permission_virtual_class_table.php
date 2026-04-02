<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionVirtualClassTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'VirtualClass%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'VirtualClass: Create',
                    'slug' => 'VirtualClass.create',
                    'description' => 'Create new VirtualClass', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'VirtualClass: Read',
                    'slug' => 'VirtualClass.read',
                    'description' => 'Read new VirtualClass', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'VirtualClass: Update',
                    'slug' => 'VirtualClass.update',
                    'description' => 'Update new VirtualClass', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'VirtualClass: Delete',
                    'slug' => 'VirtualClass.delete',
                    'description' => 'Delete new VirtualClass', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'VirtualClass%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
