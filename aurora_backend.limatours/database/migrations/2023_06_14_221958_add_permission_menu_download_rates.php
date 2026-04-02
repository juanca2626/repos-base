<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionMenuDownloadRates extends Migration
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
            $permissions_services = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_DownloadRatesServices%')->get();
            if (count($permissions_services) === 0) {
                $permission_rate_service = Permission::create([
                    'name' => 'MenuFrontEnd_DownloadRatesServices: Read',
                    'slug' => 'mfdownloadratesservices.read',
                    'description' => 'Read new Menu - Descargar Tarifas de servicios', // optional
                ]);
                $adminRole->attachPermission($permission_rate_service);
            }

            $permissions_hotels = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_DownloadRatesHotels%')->get();
            if (count($permissions_hotels) === 0) {
                $permission_rate_hotel = Permission::create([
                    'name' => 'MenuFrontEnd_DownloadRatesHotels: Read',
                    'slug' => 'mfdownloadrateshotels.read',
                    'description' => 'Read new Menu - Descargar Tarifas de hoteles', // optional
                ]);
                $adminRole->attachPermission($permission_rate_hotel);
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
        $permissions_services = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_DownloadRatesServices%')->get();
        foreach ($permissions_services as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }

        $permissions_hotels = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_DownloadRatesHotels%')->get();
        foreach ($permissions_hotels as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
