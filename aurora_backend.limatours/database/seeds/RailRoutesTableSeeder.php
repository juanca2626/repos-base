<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RailRoutesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_rail_routes = File::get("database/data/rail_routes.json");
        $rail_routes = json_decode($file_rail_routes, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($rail_routes,$created_at) {
            foreach ($rail_routes as $rail_route) {
                DB::table('rail_routes')->insert([
                    'id' => $rail_route["id"],
                    'name' => $rail_route["name"],
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
            }
        });
    }
}
