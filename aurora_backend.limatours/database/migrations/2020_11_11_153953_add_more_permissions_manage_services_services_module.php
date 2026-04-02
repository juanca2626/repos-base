<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddMorePermissionsManageServicesServicesModule extends Migration
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
                'name' => 'Manage_Services: Schedule',
                'slug' => 'manageservices.schedule',
                'description' => 'Manage - Services', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Operability',
                'slug' => 'manageservices.operability',
                'description' => 'Manage - Services', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Gallery',
                'slug' => 'manageservices.gallery',
                'description' => 'Manage - Services', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Configuration',
                'slug' => 'manageservices.configuration',
                'description' => 'Manage - Services', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Includes',
                'slug' => 'manageservices.includes',
                'description' => 'Manage - Services', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Availability',
                'slug' => 'manageservices.availability',
                'description' => 'Manage - Services', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Multiservices',
                'slug' => 'manageservices.multiservices',
                'description' => 'Manage - Services', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Equivalences',
                'slug' => 'manageservices.equivalences',
                'description' => 'Manage - Services', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Featured',
                'slug' => 'manageservices.featured',
                'description' => 'Manage - Services', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Instructions',
                'slug' => 'manageservices.instructions',
                'description' => 'Manage - Services', // optional
            ]);
            $adminRole->attachPermission($permission);

            $permission = Permission::create([
                'name' => 'Manage_Services: Supplements',
                'slug' => 'manageservices.supplements',
                'description' => 'Manage - Services', // optional
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
