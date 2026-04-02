<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionExportImportServices extends Migration
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

            $permission = Permission::create([
                'name' => 'Manage_Services: Export texts',
                'slug' => 'manageservices.export_texts',
                'description' => 'Manage - Services - Export texts', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Import texts',
                'slug' => 'manageservices.import_texts',
                'description' => 'Manage - Services - Import texts', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Import Equivalences',
                'slug' => 'manageservices.import_equivalences',
                'description' => 'Manage - Services - Import Equivalences', // optional
            ]);

            $adminRole->attachPermission($permission);

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
