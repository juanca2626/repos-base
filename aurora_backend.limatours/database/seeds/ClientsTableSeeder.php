<?php

use App\Client;
use App\Markup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        if (App::environment('local') === true) {
//            factory(Client::class)->times(10)->create();
//        }

        $file_clients = File::get("database/data/clients.json");
        $clients = json_decode($file_clients, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($clients,$created_at) {

            foreach ($clients as $client) {
                $idClient = DB::table('clients')->insertGetId([
                    'code' => $client['code'],
                    'name' => $client['name'],
                    'have_credit'=>$client['have_credit'],
                    'status' => $client['status'],
                    'market_id' => $client['market_id'],
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);

                for ( $year=2019; $year<=2021; $year++){
                    DB::table('markups')->insert([
                        'period' => $year,
                        'hotel' => $client['markup'],
                        'service'=>$client['markup'],
                        'status' => 1,
                        'client_id' => $idClient,
                        'created_at' => $created_at,
                        'updated_at' => $created_at
                    ]);
                }

            }
        });
    }
}
