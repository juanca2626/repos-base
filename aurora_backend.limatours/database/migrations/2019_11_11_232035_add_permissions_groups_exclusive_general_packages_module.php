<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsGroupsExclusiveGeneralPackagesModule extends Migration
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

            $permission = Permission::create([
                'name' => 'Packages: Exclusive',
                'slug' => 'packages.exclusive',
                'description' => '', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Packages: Generals',
                'slug' => 'packages.generals',
                'description' => '', // optional
            ]);
            $adminRole->attachPermission($permission);

        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
