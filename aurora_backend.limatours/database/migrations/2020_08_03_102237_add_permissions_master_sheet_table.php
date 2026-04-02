<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsMasterSheetTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MasterSheet%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MasterSheet: Create',
                    'slug' => 'mastersheet.create',
                    'description' => 'Create new MasterSheet', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterSheet: Read',
                    'slug' => 'mastersheet.read',
                    'description' => 'Read new MasterSheet', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterSheet: Update',
                    'slug' => 'mastersheet.update',
                    'description' => 'Update new MasterSheet', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterSheet: Delete',
                    'slug' => 'mastersheet.delete',
                    'description' => 'Delete new MasterSheet', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MasterSheet%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
