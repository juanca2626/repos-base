<?php

use App\Market;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class MarketsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        if (App::environment('local') === true) {
//            factory(Market::class)->times(8)->create();
//        }

        $file_markets = File::get("database/data/markets.json");
        $markets = json_decode($file_markets, true);
        foreach ($markets as $market) {
            Market::create($market);
        }
    }
}
