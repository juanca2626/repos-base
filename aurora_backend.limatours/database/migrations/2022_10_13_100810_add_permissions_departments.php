<?php


use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

/**
 * Class AddPermissionsDepartments
 */
class AddPermissionsDepartments extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Departments%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'Departments: Create',
                    'slug' => 'departments.create',
                    'description' => 'Create new Departments', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Departments: Read',
                    'slug' => 'departments.read',
                    'description' => 'Read new Departments', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Departments: Update',
                    'slug' => 'departments.update',
                    'description' => 'Update new Departments', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Departments: Delete',
                    'slug' => 'departments.delete',
                    'description' => 'Delete new Departments', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Departments%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
