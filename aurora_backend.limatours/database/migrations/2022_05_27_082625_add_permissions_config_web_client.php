<?php


use Illuminate\Database\Migrations\Migration;
use jeremykenedy\LaravelRoles\Models\Permission;

class AddPermissionsConfigWebClient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = config('roles.models.permission')::where('name', 'LIKE', 'ConfigWebClient%')->get();
        if (count($permissions) === 0) {
            Permission::create([
                'name' => 'ConfigWebClient: Create',
                'slug' => 'configwebclient.create',
                'description' => 'Create new Configuration Client Ecommerce', // optional
            ]);

            Permission::create([
                'name' => 'ConfigWebClient: Read',
                'slug' => 'configwebclient.read',
                'description' => 'Read new Configuration Client Ecommerce', // optional
            ]);

            Permission::create([
                'name' => 'ConfigWebClient: Update',
                'slug' => 'configwebclient.update',
                'description' => 'Update new Configuration Client Ecommerce', // optional
            ]);

            Permission::create([
                'name' => 'ConfigWebClient: Delete',
                'slug' => 'configwebclient.delete',
                'description' => 'Delete new Configuration Client Ecommerce', // optional
            ]);

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
