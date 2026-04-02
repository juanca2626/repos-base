<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsSubstituteUsersMenuFrontend extends Migration
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
            //files_query
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_SubstituteUsers%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_SubstituteUsers: Read',
                    'slug' => 'mfsubstituteusers.read',
                    'description' => 'Read new Menu - Tracking Substitute Users', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
