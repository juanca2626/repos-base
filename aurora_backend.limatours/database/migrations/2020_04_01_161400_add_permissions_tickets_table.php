<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsTicketsTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'Tickets%')->get();

            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'Tickets Create',
                    'slug' => 'tickets.create',
                    'description' => 'Create new Ticket', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Tickets: Read',
                    'slug' => 'tickets.read',
                    'description' => 'Read new Ticket', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Tickets: Update',
                    'slug' => 'tickets.update',
                    'description' => 'Update new Ticket', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'Tickets: Delete',
                    'slug' => 'tickets.delete',
                    'description' => 'Delete new Ticket', // optional
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
