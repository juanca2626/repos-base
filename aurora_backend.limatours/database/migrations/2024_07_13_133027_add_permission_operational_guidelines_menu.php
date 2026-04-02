<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionOperationalGuidelinesMenu extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'OperationalGuidelines%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'OperationalGuidelines: Create',
                    'slug' => 'operationalguidelines.create',
                    'description' => 'Create new Pautas operativas', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'OperationalGuidelines: Read',
                    'slug' => 'operationalguidelines.read',
                    'description' => 'Read new Pautas operativas', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'OperationalGuidelines: Update',
                    'slug' => 'operationalguidelines.update',
                    'description' => 'Update new Pautas operativas', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'OperationalGuidelines: Delete',
                    'slug' => 'operationalguidelines.delete',
                    'description' => 'Delete new Pautas operativas', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'OperationalGuidelines%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
