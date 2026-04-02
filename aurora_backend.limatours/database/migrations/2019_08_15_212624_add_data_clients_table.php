<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
