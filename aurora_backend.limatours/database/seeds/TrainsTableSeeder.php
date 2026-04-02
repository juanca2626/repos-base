<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_trains = File::get("database/data/trains.json");
        $trains = json_decode($file_trains, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($trains,$created_at) {
            foreach ($trains as $train) {
                DB::table('trains')->insert([
                    'id' => $train["id"],
                    'code' => $train["code"],
                    'name' => $train["name"],
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
            }
        });
    }
}
