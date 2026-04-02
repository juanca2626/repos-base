<?php


use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsMenuFrontend extends Migration
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
            //Desbloquear File
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_UnlockFileStela%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_UnlockFileStela: Read',
                    'slug' => 'mfunlockfilestela.read',
                    'description' => 'Read new Menu - Desbloquear File Stela', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_UnlockFileStela%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
