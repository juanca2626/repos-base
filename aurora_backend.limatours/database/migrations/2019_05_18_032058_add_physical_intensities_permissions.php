<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPhysicalIntensitiesPermissions extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PhysicalIntensities%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'PhysicalIntensities: Create',
                    'slug' => 'physicalintensities.create',
                    'description' => 'Create new PhysicalIntensities', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PhysicalIntensities: Read',
                    'slug' => 'physicalintensities.read',
                    'description' => 'Read new PhysicalIntensities', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PhysicalIntensities: Update',
                    'slug' => 'physicalintensities.update',
                    'description' => 'Update new PhysicalIntensities', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'PhysicalIntensities: Delete',
                    'slug' => 'physicalintensities.delete',
                    'description' => 'Delete new PhysicalIntensities', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'PhysicalIntensities%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
