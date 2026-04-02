<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsHotelInformationTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'hotelinformation%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'hotelinformation: Create',
                    'slug' => 'hotelinformation.create',
                    'description' => 'Create new hotelinformation', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelinformation: Read',
                    'slug' => 'hotelinformation.read',
                    'description' => 'Read new hotelinformation', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelinformation: Update',
                    'slug' => 'hotelinformation.update',
                    'description' => 'Update new hotelinformation', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelinformation: Delete',
                    'slug' => 'hotelinformation.delete',
                    'description' => 'Delete new hotelinformation', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'hotelinformation%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
