<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsServiceSubCategoriesTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceSubCategories%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ServiceSubCategories: Create',
                    'slug' => 'servicesubcategories.create',
                    'description' => 'Create new Service Sub Categories', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceSubCategories: Read',
                    'slug' => 'servicesubcategories.read',
                    'description' => 'Read new Service Sub Categories', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceSubCategories: Update',
                    'slug' => 'servicesubcategories.update',
                    'description' => 'Update new Chains', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceSubCategories: Delete',
                    'slug' => 'servicesubcategories.delete',
                    'description' => 'Delete new Service Sub Categories', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceSubCategories%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
