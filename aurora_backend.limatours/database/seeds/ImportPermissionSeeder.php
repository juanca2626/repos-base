<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Role;
use jeremykenedy\LaravelRoles\Models\Permission;
use App\PermissionModule;

class ImportPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            // Buscar el permission_module con name LIKE '%hoteles%'
            $permissionModule = PermissionModule::where('name', 'LIKE', '%hoteles%')->first();

            if (!$permissionModule) {
                $this->command->warn('⚠️ No se encontró un permission_module con name LIKE "%hoteles%"');
            }

            $permissions = [
                [
                    'name'          => 'HotelsImportHyperguest: Create',
                    'slug'          => 'hotelsimporthyperguest.create',
                    'description'   => 'Import new Hyperguest Hotels',
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'HotelsImportHyperguest: Read',
                    'slug'          => 'hotelsimporthyperguest.read',
                    'description'   => 'Import new Hyperguest Hotels',
                    'created_at'    => now(),
                ]
            ];

            foreach ($permissions as $permission) {
                DB::table('permissions')->updateOrInsert(
                    ['slug' => $permission['slug']],
                    $permission
                );
                 
            }

            $adminRole = Role::where('slug', '=', 'admin')->first();
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'HotelsImportHyperguest%')->get();
            foreach ($permissions as $permission) {
                if (!$adminRole->permissions->contains($permission->id)) {
                    $adminRole->attachPermission($permission);
                }
            }

            $this->command->info('Creación de permisos de importación de hoteles Hyperguest y asignación al rol ADMIN completada');
        });
    }
}

