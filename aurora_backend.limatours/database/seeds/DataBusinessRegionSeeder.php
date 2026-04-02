<?php

use Illuminate\Database\Seeder;

class DataBusinessRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BusinessRegionSeeder::class,
            BusinessRegionClientSeeder::class,
            UpdateMarkupsBusinessRegionSeeder::class,
            UpdateClientExecutivesBusinessRegionSeeder::class,
            // UpdateClientRatePlansBusinessRegionSeeder::class, -- Error rate_plan_id
            // UpdateHotelClientsBusinessRegionSeeder::class,
            AssignBusinessRegionToUsersSeeder::class,
            UpdateServiceClientsBusinessRegionSeeder::class,
            // AddChannelsSeeder::class,
            ChannelHotelAndRoomTypeSeeder::class,
            ClientPackageDefaultSeeder::class,
            ImportPermissionSeeder::class,
            ImportHotelHyperguestPullSeeder::class,
            RegionalizacionPermisionModuleSeeder::class
        ]);
    }
}
