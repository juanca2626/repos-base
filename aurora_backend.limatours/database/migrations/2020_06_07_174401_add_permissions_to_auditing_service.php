<?php


use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsToAuditingService extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'AuditServices%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'AuditServices: Create',
                    'slug' => 'auditservices.create',
                    'description' => 'Create new Audit Services', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'AuditServices: Read',
                    'slug' => 'auditservices.read',
                    'description' => 'Read new Audit Services', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'AuditServices: Update',
                    'slug' => 'auditservices.update',
                    'description' => 'Update new Audit Services', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'AuditServices: Delete',
                    'slug' => 'auditservices.delete',
                    'description' => 'Delete new Audit Services', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'AuditServices%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
