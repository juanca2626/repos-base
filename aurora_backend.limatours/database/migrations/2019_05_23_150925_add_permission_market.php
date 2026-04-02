<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class AddPermissionMarket extends Migration
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
          $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Markets%')->get();

          if (count($permissions) === 0) {
              $permission = Permission::create([
                  'name' => 'Markets: Create',
                  'slug' => 'markets.create',
                  'description' => 'Create new Markets', // optional
              ]);
              $adminRole->attachPermission($permission);

              $permission = Permission::create([
                  'name' => 'Markets: Read',
                  'slug' => 'markets.read',
                  'description' => 'Read new Markets', // optional
              ]);
              $adminRole->attachPermission($permission);

              $permission = Permission::create([
                  'name' => 'Markets: Update',
                  'slug' => 'markets.update',
                  'description' => 'Update new Markets', // optional
              ]);
              $adminRole->attachPermission($permission);

              $permission = Permission::create([
                  'name' => 'Markets: Delete',
                  'slug' => 'markets.delete',
                  'description' => 'Delete new Markets', // optional
              ]);
              $adminRole->attachPermission($permission);
          }
      }  //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $adminRole = Role::where('slug', '=', 'admin')->first();

        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Markets%')->get();

        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);

            $permission->delete();
        }//
    }
}
