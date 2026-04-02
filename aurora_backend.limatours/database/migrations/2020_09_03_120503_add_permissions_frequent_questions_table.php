<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsFrequentQuestionsTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'FrequentQuestions%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'FrequentQuestions: Create',
                    'slug' => 'frequentquestions.create',
                    'description' => 'Create new Frequent Questions', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'FrequentQuestions: Read',
                    'slug' => 'frequentquestions.read',
                    'description' => 'Read new Frequent Questions', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'FrequentQuestions: Update',
                    'slug' => 'frequentquestions.update',
                    'description' => 'Update new Frequent Questions', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'FrequentQuestions: Delete',
                    'slug' => 'frequentquestions.delete',
                    'description' => 'Delete new Frequent Questions', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'FrequentQuestions%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
