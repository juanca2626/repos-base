<?php

use App\Market;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataMarketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $file_markets = File::get("database/data/markets.json");
        $markets = json_decode($file_markets, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($markets,$created_at) {
            foreach ($markets as $market) {
                DB::table('markets')->insert([
                    'id' => $market['id'],
                    'name' => $market['name'],
                    'code' => $market['code'],
                    'status' => $market['status'],
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
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
