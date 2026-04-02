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
            //files_query
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_TrackingStela%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_TrackingStela: Read',
                    'slug' => 'mftrackingstela.read',
                    'description' => 'Read new Menu - Tracking Programacion Stela', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
            //executive_board
            $permissions = config('roles.models.permission')::where('name', 'LIKE',
                'MenuFrontEnd_CheckPaymentSupplierStela%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_CheckPaymentSupplierStela: Read',
                    'slug' => 'mfcheckpaymentsupplier.read',
                    'description' => 'Read new Menu - Consulta Pagos Prv Stela', // optional
                ]);
                $adminRole->attachPermission($permission);
            }
            //files_management
            $permissions = config('roles.models.permission')::where('name', 'LIKE',
                'MenuFrontEnd_ObservedAccountingDocumentsStela%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'MenuFrontEnd_ObservedAccountingDocumentsStela: Read',
                    'slug' => 'mfobservedaccountingdocumentsstela.read',
                    'description' => 'Read new Menu - Doc. Contables Obs. Stela', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'MenuFrontEnd_%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
