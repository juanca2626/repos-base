<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainRailRoutesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_train_rail_routes = File::get("database/data/train_rail_routes.json");
        $train_rail_routes = json_decode($file_train_rail_routes, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($train_rail_routes,$created_at) {
            foreach ($train_rail_routes as $train_rail_route) {
                DB::table('train_rail_routes')->insert([
                    'id' => $train_rail_route["id"],
                    'train_id' => $train_rail_route["train_id"],
                    'rail_route_id' => $train_rail_route["rail_route_id"],
                    'code' => $train_rail_route["code"],
                    'abbreviation' => $train_rail_route["abbreviation"],
                    'name' => $train_rail_route["name"],
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
            }
        });
    }
}
