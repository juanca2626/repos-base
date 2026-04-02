<?php

use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddPermissionsQuestionCategoriesTable extends Migration
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'QuestionCategories%')->get();
            if (count($permissions) === 0) {
                $permission = Permission::create([
                    'name' => 'QuestionCategories: Create',
                    'slug' => 'questioncategories.create',
                    'description' => 'Create new Question Categories', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'QuestionCategories: Read',
                    'slug' => 'questioncategories.read',
                    'description' => 'Read new Question Categories', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'QuestionCategories: Update',
                    'slug' => 'questioncategories.update',
                    'description' => 'Update new Question Categories', // optional
                ]);
                $adminRole->attachPermission($permission);

                $permission = Permission::create([
                    'name' => 'QuestionCategories: Delete',
                    'slug' => 'questioncategories.delete',
                    'description' => 'Delete new Question Categories', // optional
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
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'QuestionCategories%')->get();
        foreach ($permissions as $permission) {
            $adminRole->detachPermission($permission);
            $permission->delete();
        }
    }
}
