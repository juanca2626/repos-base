<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsToAuditingClient extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'AuditClients%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'AuditClients: Create',
                    'slug' => 'auditclients.create',
                    'description' => 'Create new Audit Clients', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'AuditClients: Read',
                    'slug' => 'auditclients.read',
                    'description' => 'Read new Audit Clients', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'AuditClients: Update',
                    'slug' => 'auditclients.update',
                    'description' => 'Update new Audit Clients', // optional
                ]);
                $adminRole->attachPermission($permission);
                $permission = Permission::create([
                    'name' => 'AuditClients: Delete',
                    'slug' => 'auditclients.delete',
                    'description' => 'Delete new Audit Clients', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'AuditClients%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
