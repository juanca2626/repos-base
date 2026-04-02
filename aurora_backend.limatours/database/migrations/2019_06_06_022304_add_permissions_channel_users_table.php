<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPermissionsChannelUsersTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ChannelUsers%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ChannelUsers: Create',
                    'slug' => 'ChannelUsers.create',
                    'description' => 'Create new ChannelUsers', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ChannelUsers: Read',
                    'slug' => 'ChannelUsers.read',
                    'description' => 'Read new ChannelUsers', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ChannelUsers: Update',
                    'slug' => 'ChannelUsers.update',
                    'description' => 'Update new ChannelUsers', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ChannelUsers: Delete',
                    'slug' => 'ChannelUsers.delete',
                    'description' => 'Delete new ChannelUsers', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ChannelUsers%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
