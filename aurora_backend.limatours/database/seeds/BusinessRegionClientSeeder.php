<?php

use App\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessRegionClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = Client::all();

        $insertData = $clients->map(function($client) {
            return [
                'business_region_id' => 1,
                'client_id' => $client->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->toArray();

        DB::table('business_region_client')->insert($insertData);

        $this->command->info('Asignación de BusinessRegion a Client completada.');
    }
}
