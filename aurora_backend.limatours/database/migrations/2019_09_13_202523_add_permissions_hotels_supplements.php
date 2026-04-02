<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsHotelsSupplements extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'hotelsupplements%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'hotelsupplements: Create',
                    'slug' => 'hotelsupplements.create',
                    'description' => 'Create new hotelsupplements', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelsupplements: Read',
                    'slug' => 'hotelsupplements.read',
                    'description' => 'Read new hotelsupplements', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelsupplements: Update',
                    'slug' => 'hotelsupplements.update',
                    'description' => 'Update new hotelsupplements', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelsupplements: Delete',
                    'slug' => 'hotelsupplements.delete',
                    'description' => 'Delete new hotelsupplements', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'hotelsupplements%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}


