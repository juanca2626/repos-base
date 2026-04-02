<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsClientsMenuFrontend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $adminRole = Role::where('slug', '=', 'cl')->first();

        if ($adminRole !== null) {
            //Hoteles
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_Hotels%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_Hotels: Read',
                    'slug' => 'mfhotels.read',
                    'description' => 'Read new Menu - Hoteles', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
            //Servicios
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_Services%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_Services: Read',
                    'slug' => 'mfservices.read',
                    'description' => 'Read new Menu - Servicios', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
            //Paquetes
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_Packages%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_Packages: Read',
                    'slug' => 'mfpackages.read',
                    'description' => 'Read new Menu - Paquetes', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
            //Reporte Reservas
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_ReportReserves%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_ReportReserves: Read',
                    'slug' => 'mfreport.read',
                    'description' => 'Read new Menu - Reporte Reservas', // optional
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
        //
    }
}
