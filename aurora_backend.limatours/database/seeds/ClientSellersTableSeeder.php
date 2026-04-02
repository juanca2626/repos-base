<?php

use App\ClientSeller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class ClientSellersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        if (App::environment('local') === true) {
//            factory(ClientSeller::class)->times(10)->create();
//        }

        $path = 'database/data/sql/client_sellers.sql';
        DB::transaction(function () use ($path) {
            DB::unprepared(file_get_contents($path));
        });

    }
}
