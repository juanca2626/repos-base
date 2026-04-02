<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsMasterServicesTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MasterServices%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MasterServices: Create',
                    'slug' => 'masterservices.create',
                    'description' => 'Create new Master Services', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServices: Read',
                    'slug' => 'masterservices.read',
                    'description' => 'Read new Master Services', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServices: Update',
                    'slug' => 'masterservices.update',
                    'description' => 'Update new Master Services', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServices: Delete',
                    'slug' => 'masterservices.delete',
                    'description' => 'Delete new Master Services', // optional
                ]);
                $adminRole->attachPermission($permission);
                //-------------------------------------------------------------------

                $permission = Permission::create([
                    'name' => 'MasterServicesEquivalences: Create',
                    'slug' => 'masterservicesequivalences.create',
                    'description' => 'Create new Master Services Equivalences', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServicesEquivalences: Read',
                    'slug' => 'masterservicesequivalences.read',
                    'description' => 'Read new Master Services Equivalences', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServicesEquivalences: Update',
                    'slug' => 'masterservicesequivalences.update',
                    'description' => 'Update new Master Services Equivalences', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServicesEquivalences: Delete',
                    'slug' => 'masterservicesequivalences.delete',
                    'description' => 'Delete new Master Services Equivalences', // optional
                ]);
                $adminRole->attachPermission($permission);
                //-------------------------------------------------------------------

                $permission = Permission::create([
                    'name' => 'MasterServicesReleased: Create',
                    'slug' => 'masterservicesreleased.create',
                    'description' => 'Create new Master Services Released', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServicesReleased: Read',
                    'slug' => 'masterservicesreleased.read',
                    'description' => 'Read new Master Services Released', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServicesReleased: Update',
                    'slug' => 'masterservicesreleased.update',
                    'description' => 'Update new Master Services Released', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServicesReleased: Delete',
                    'slug' => 'masterservicesreleased.delete',
                    'description' => 'Delete new Master Services Released', // optional
                ]);
                $adminRole->attachPermission($permission);
                //-------------------------------------------------------------------

                $permission = Permission::create([
                    'name' => 'MasterServicesComponents: Create',
                    'slug' => 'masterservicescomponents.create',
                    'description' => 'Create new Master Services Components', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServicesComponents: Read',
                    'slug' => 'masterservicescomponents.read',
                    'description' => 'Read new Master Services Components', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServicesComponents: Update',
                    'slug' => 'masterservicescomponents.update',
                    'description' => 'Update new Master Services Components', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'MasterServicesComponents: Delete',
                    'slug' => 'masterservicescomponents.delete',
                    'description' => 'Delete new Master Services Components', // optional
                ]);
                $adminRole->attachPermission($permission);
                //-------------------------------------------------------------------

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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceTypeActivities%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
