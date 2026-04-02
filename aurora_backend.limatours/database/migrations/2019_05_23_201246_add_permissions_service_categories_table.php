<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsServiceCategoriesTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceCategories%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'ServiceCategories: Create',
                    'slug' => 'servicecategories.create',
                    'description' => 'Create new Service Categories', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceCategories: Read',
                    'slug' => 'servicecategories.read',
                    'description' => 'Read new Service Categories', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceCategories: Update',
                    'slug' => 'servicecategories.update',
                    'description' => 'Update new Service Categories', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'ServiceCategories: Delete',
                    'slug' => 'servicecategories.delete',
                    'description' => 'Delete new Service Categories', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ServiceCategories%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
