<?php

use App\PermissionModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionalizacionPermisionModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       
        $permissionModuleRegions = PermissionModule::where('slug', 'LIKE', '%regions%')->first();
      
        $permissionRecords = DB::table('permissions')
            ->where(function ($query) {
                $query->where('slug', 'like', '%HotelsImportHyperguest%')
                    ->orWhere('slug', 'like', '%BusinessRegion%');
            })
            ->get();
 
        foreach($permissionRecords as $permissionRecord){
                         
            DB::table('permission_details')->updateOrInsert(
                ['permission_id' => $permissionRecord->id],
                [
                    'permission_id' => $permissionRecord->id,
                    'permission_module_id' => $permissionModuleRegions->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    
    }
}
