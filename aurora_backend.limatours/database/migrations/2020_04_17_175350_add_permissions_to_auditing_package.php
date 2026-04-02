<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsToAuditingPackage extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'AuditPackages%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'AuditPackages: Create',
                    'slug' => 'auditpackages.create',
                    'description' => 'Create new AuditPackages', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'AuditPackages: Read',
                    'slug' => 'auditpackages.read',
                    'description' => 'Read new AuditPackages', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'AuditPackages: Update',
                    'slug' => 'auditpackages.update',
                    'description' => 'Update new AuditPackages', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'AuditPackages: Delete',
                    'slug' => 'auditpackages.delete',
                    'description' => 'Delete new AuditPackages', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'AuditPackages%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
