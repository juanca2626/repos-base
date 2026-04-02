<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsHotelGalleryTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'hotelgallery%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'hotelgallery: Create',
                    'slug' => 'hotelgallery.create',
                    'description' => 'Create new hotelgallery', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelgallery: Read',
                    'slug' => 'hotelgallery.read',
                    'description' => 'Read new hotelgallery', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelgallery: Update',
                    'slug' => 'hotelgallery.update',
                    'description' => 'Update new hotelgallery', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'hotelgallery: Delete',
                    'slug' => 'hotelgallery.delete',
                    'description' => 'Delete new hotelgallery', // optional
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

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'hotelgallery%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }
    }
}
