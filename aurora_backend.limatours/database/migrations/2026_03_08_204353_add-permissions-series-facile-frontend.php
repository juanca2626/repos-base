<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsSeriesFacileFrontend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $adminRole = Role::where('slug', '=', 'cl')->first();
        $moduleId     = 27;

        if ($adminRole !== null) {
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Menu Frontend Series Facile:%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'Menu Frontend Series Facile (Client): Read',
                    'slug' => 'mfseriesfacile.read',
                    'description' => 'Read new Menu - Series Facile',
                ]);

                DB::table('permission_details')->insert([
                    'permission_id'        => $permission->id,
                    'permission_module_id' => $moduleId
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
        $modulePrefix = 'mfseriesfacile';
        // Buscamos todos los permisos que empiecen con el prefijo
        $permissions = Permission::where('slug', 'LIKE', $modulePrefix . '.%')->get();
        foreach ($permissions as $permission) {
            // 1. Eliminar relación con permission_details
            DB::table('permission_details')->where('permission_id', $permission->id)->delete();
            // 2. Desvincular de roles (LaravelRoles suele manejar esto en cascade, pero por seguridad)
            $permission->roles()->sync([]);
            // 3. Eliminar permiso
            $permission->delete();
        }
    }
}
