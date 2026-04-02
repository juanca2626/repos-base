<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsCancellationPoliciesTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'CancellationPolicies%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'CancellationPolicies: Create',
                    'slug' => 'cancellationpolicies.create',
                    'description' => 'Create new Cancellation Policies', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'CancellationPolicies: Read',
                    'slug' => 'cancellationpolicies.read',
                    'description' => 'Read new Cancellation Policies', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'CancellationPolicies: Update',
                    'slug' => 'cancellationpolicies.update',
                    'description' => 'Update new Cancellation Policies', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'CancellationPolicies: Delete',
                    'slug' => 'cancellationpolicies.delete',
                    'description' => 'Delete new Cancellation Policies', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'CancellationPolicies%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
