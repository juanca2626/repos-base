<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsPhotosTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Photos%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'Photos Create',
                    'slug' => 'photos.create',
                    'description' => 'Create new Photos', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Photos: Read',
                    'slug' => 'photos.read',
                    'description' => 'Read new Photos', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Photos: Update',
                    'slug' => 'photos.update',
                    'description' => 'Update new Photos', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Photos: Delete',
                    'slug' => 'photos.delete',
                    'description' => 'Delete new Photos', // optional
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
