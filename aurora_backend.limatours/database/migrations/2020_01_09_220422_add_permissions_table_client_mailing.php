<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsTableClientMailing extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MasiMailing%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MasiMailing: Show',
                    'slug' => 'masimailing.show',
                    'description' => 'Show Client Mailing module', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MasiMailing%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
