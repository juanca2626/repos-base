<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;


class AddPermissionsMenuOrderFrontend extends Migration
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
            //Mantenimiento del control de pedidos
            $permissions = config('roles.models.permission')::where('name', 'LIKE',
                'MenuFrontEnd_OrderControlMaintenance%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_OrderControlMaintenance: Read',
                    'slug' => 'mfordercontrolmaintenance.read',
                    'description' => 'Read new Menu - Mantenimiento del control de pedidos', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
            //Usuario del control de pedidos
            $permissions = config('roles.models.permission')::where('name', 'LIKE',
                'MenuFrontEnd_OrderControlUser%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_OrderControlUser: Read',
                    'slug' => 'mfordercontroluser.read',
                    'description' => 'Read new Menu - Usuario del control de pedidos', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
            //Reporte de control de pedidos
            $permissions = config('roles.models.permission')::where('name', 'LIKE',
                'MenuFrontEnd_OrderControlReport%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_OrderControlReport: Read',
                    'slug' => 'mfordercontrolreport.read',
                    'description' => 'Read new Menu - Reporte de control de pedidos ', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
            //Dashbord del control de pedidos
            $permissions = config('roles.models.permission')::where('name', 'LIKE',
                'MenuFrontEnd_OrderControlDashbord%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_OrderControlDashbord: Read',
                    'slug' => 'mfordercontroldashbord.read',
                    'description' => 'Read new Menu - Dashbord del control de pedidos', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
            //Reporte de Cuadre de File
            $permissions = config('roles.models.permission')::where('name', 'LIKE',
                'MenuFrontEnd_FileQuadReport%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_FileQuadReport: Read',
                    'slug' => 'mffilequadreport.read',
                    'description' => 'Read new Menu - Reporte de Cuadre de File', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_Order%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
