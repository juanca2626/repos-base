<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsMultimediaClientsMenuFrontend extends Migration
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
            //Fotos
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_Photos%')->first();
            $adminRole->attachPermission($permissions);

            //Videos
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_Videos%')->first();
            $adminRole->attachPermission($permissions);

            //Revistas
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_Journals%')->first();
            $adminRole->attachPermission($permissions);

            //Cotizacion
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_QuotationBoard%')->first();
            $adminRole->attachPermission($permissions);

            //Consulta Files
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_FilesQuery%')->first();
            $adminRole->attachPermission($permissions);
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
