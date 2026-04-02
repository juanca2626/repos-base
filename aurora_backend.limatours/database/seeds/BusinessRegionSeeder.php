<?php

use App\BusinessRegion;
use App\BusinessRegionsCountry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Role;
use jeremykenedy\LaravelRoles\Models\Permission;

class BusinessRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // BusinessRegion::truncate();

            $businesRegionLito = BusinessRegion::create([
                'description' => 'Región Perú'
            ]);

            $businesRegionEcuador = BusinessRegion::create([
                'description' => 'Región Ecuador'
            ]);

            $businesRegionArgentina = BusinessRegion::create([
                'description' => 'Región Argentina'
            ]);

            $businesRegionColombia = BusinessRegion::create([
                'description' => 'Región Colombia'
            ]);

            $permissions = [
                [
                    'name'          => 'BusinessRegion: Create',
                    'slug'          => 'businessregion.create',
                    'description'   => 'BusinessRegion new Create',
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'BusinessRegion: Read',
                    'slug'          => 'businessregion.read',
                    'description'   => 'BusinessRegion new Read',
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'BusinessRegion: Update',
                    'slug'          => 'businessregion.update',
                    'description'   => 'BusinessRegion new Update',
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'BusinessRegion: Delete',
                    'slug'          => 'businessregion.delete',
                    'description'   => 'BusinessRegion new Delete',
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
            $permissions = config('roles.models.permission')::where('name', 'LIKE', 'BusinessRegion%')->get();
            foreach ($permissions as $permission) {

                if (!$adminRole->permissions->contains($permission->id)) {
                    $adminRole->attachPermission($permission);
                }
            }

            // BusinessRegionsCountry::truncate();

            BusinessRegionsCountry::create(['business_region_id'=> $businesRegionLito->id,'country_id'=> 89]);
            BusinessRegionsCountry::create(['business_region_id'=> $businesRegionLito->id,'country_id'=> 123]);
            BusinessRegionsCountry::create(['business_region_id'=> $businesRegionLito->id,'country_id'=> 12]);
            BusinessRegionsCountry::create(['business_region_id'=> $businesRegionLito->id,'country_id'=> 81]);
            BusinessRegionsCountry::create(['business_region_id'=> $businesRegionEcuador->id,'country_id'=> 103]);
            BusinessRegionsCountry::create(['business_region_id'=> $businesRegionArgentina->id,'country_id'=> 5]);
            BusinessRegionsCountry::create(['business_region_id'=> $businesRegionColombia->id, 'country_id'=> 82]);

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->command->info('Creación de regiones de negocio y asignación de permisos completada');
        });

    }
}
