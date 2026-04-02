<?php

use App\PermissionDetail;
use App\PermissionModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionDetailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customer_service = PermissionModule::where('slug', 'customer-service')->first();
        $adventure = PermissionModule::where('slug', 'adventure')->first();
        $operations = PermissionModule::where('slug', 'operation')->first();

        $links_customer_service = [
            'Claims',
            'Reports',
        ];

        $links_adventure = [
            'ACategories',
            'ASettings',
            'Templates',
            'Departures',
            'Entrances',
            'Cash',
            'Programming',
            'Manifest',
            'ACalendar',
            'AServices',
        ];

        $permissions_customer_service = DB::table('permissions')
            ->where(function ($query) use ($links_customer_service) {
                foreach ($links_customer_service as $link_customer_service) {
                    $query->orWhere('name', 'LIKE', "{$link_customer_service}%");
                }
            })
            ->get();;

        $permissions_adventure = DB::table('permissions')
            ->where(function ($query) use ($links_adventure) {
                foreach ($links_adventure as $link_adventure) {
                    $query->orWhere('name', 'LIKE', "{$link_adventure}%");
                }
            })
            ->get();

        $links_operation = [
            'ManagementReports',
        ];

        $permissions_operation = DB::table('permissions')
            ->where(function ($query) use ($links_operation) {
                foreach ($links_operation as $link_operation) {
                    $query->orWhere('name', 'LIKE', "{$link_operation}%");
                }
            })
            ->get();

        foreach ($permissions_customer_service as $permission) {
            PermissionDetail::firstOrCreate([
                'permission_id' => $permission->id,
                'permission_module_id' => $customer_service->id,
            ]);
        }

        foreach ($permissions_adventure as $permission) {
            PermissionDetail::firstOrCreate([
                'permission_id' => $permission->id,
                'permission_module_id' => $adventure->id,
            ]);
        }

        foreach ($permissions_operation as $permission) {
            PermissionDetail::firstOrCreate([
                'permission_id' => $permission->id,
                'permission_module_id' => $operations->id,
            ]);
        }
    }
}
