<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsSeriesFacile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $moduleName   = 'Series Facile';
        $permissionModuleId = DB::table('permission_modules')->insertGetId(
            ['name' => 'Series Facile', 'slug' => 'series-facile', 'kind' => 'primary','sort_order' => 34]
        );

        $allAdmins = ['admin', 'js'];

        $permissionsList = [
            [
                'action' => 'Create',
                'slug' => 'seriesfacile.create',
                'desc' => 'Create new Series Facile',
                'roles' => $allAdmins
            ],
            [
                'action' => 'Read',
                'slug' => 'seriesfacile.read',
                'desc' => 'Read new Series Facile',
                'roles' => $allAdmins
            ],
            [
                'action' => 'Update',
                'slug' => 'seriesfacile.update',
                'desc' => 'Update new Series Facile',
                'roles' => $allAdmins
            ],
            [
                'action' => 'Delete',
                'slug' => 'seriesfacile.delete',
                'desc' => 'Delete new Series Facile',
                'roles' => $allAdmins
            ],
        ];

        foreach ($permissionsList as $item) {
            if (Permission::where('slug', $item['slug'])->first() === null) {
                $permission = Permission::create([
                    'name'        => $moduleName . ': ' . $item['action'],
                    'slug'        => $item['slug'],
                    'description' => $item['desc'],
                ]);

                // 2. Insertar en tabla intermedia 'permission_details'
                // Usamos DB::table directo porque es una tabla pivote custom sin modelo aparente
                DB::table('permission_details')->insert([
                    'permission_id'        => $permission->id,
                    'permission_module_id' => $permissionModuleId
                ]);

                // 3. Asignar a Roles correspondientes
                if (!empty($item['roles'])) {
                    $rolesToAttach = Role::whereIn('slug', $item['roles'])->get();
                    foreach ($rolesToAttach as $role) {
                        $role->attachPermission($permission);
                    }
                }
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
        $slug = 'seriesfacile';

        // Buscamos todos los permisos que empiecen con el prefijo
        $permissions = Permission::where('slug', 'LIKE', $slug . '.%')->get();

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
