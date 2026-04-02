<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;
class AddPermissionServiceSchedulingMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceScheduling%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ServiceScheduling: Create',
                    'slug' => 'servicescheduling.create',
                    'description' => 'Create new Programación de servicios', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceScheduling: Read',
                    'slug' => 'servicescheduling.read',
                    'description' => 'Read new Programación de servicios', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceScheduling: Update',
                    'slug' => 'servicescheduling.update',
                    'description' => 'Update new Programación de servicios', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceScheduling: Delete',
                    'slug' => 'servicescheduling.delete',
                    'description' => 'Delete new Programación de servicios', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceScheduling%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }

}
