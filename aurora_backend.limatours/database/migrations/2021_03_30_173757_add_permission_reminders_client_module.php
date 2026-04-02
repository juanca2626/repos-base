<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionRemindersClientModule extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientReminders%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ClientReminders: Create',
                    'slug' => 'clientreminders.create',
                    'description' => 'Create new ClientReminders', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ClientReminders: Read',
                    'slug' => 'clientreminders.read',
                    'description' => 'Read new ClientReminders', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ClientReminders: Update',
                    'slug' => 'clientreminders.update',
                    'description' => 'Update new ClientReminders', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ClientReminders: Delete',
                    'slug' => 'clientreminders.delete',
                    'description' => 'Delete new ClientReminders', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ClientReminders%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
