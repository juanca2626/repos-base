<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddFixedOutputsPermissions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'FixedOutputs%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'FixedOutputs: Create',
                    'slug' => 'fixedoutputs.create',
                    'description' => 'Create new FixedOutputs', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'FixedOutputs: Read',
                    'slug' => 'fixedoutputs.read',
                    'description' => 'Read new FixedOutputs', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'FixedOutputs: Update',
                    'slug' => 'fixedoutputs.update',
                    'description' => 'Update new FixedOutputs', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'FixedOutputs: Delete',
                    'slug' => 'fixedoutputs.delete',
                    'description' => 'Delete new FixedOutputs', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'FixedOutputs%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
