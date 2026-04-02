<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsPackageCancellationPoliciesTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE',
                'PackageCancellationPolicies%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'PackageCancellationPolicies: Create',
                    'slug' => 'packagecancellationpolicies.create',
                    'description' => 'Create new Package Cancellation Policies', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageCancellationPolicies: Read',
                    'slug' => 'packagecancellationpolicies.read',
                    'description' => 'Read Package Cancellation Policies', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageCancellationPolicies: Update',
                    'slug' => 'packagecancellationpolicies.update',
                    'description' => 'Update Package Cancellation Policies', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PackageCancellationPolicies: Delete',
                    'slug' => 'packagecancellationpolicies.delete',
                    'description' => 'Delete Package Cancellation Policies', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PackageCancellationPolicies%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }

}
