<?php

use Illuminate\Database\Seeder;

/**
 *  Class SeriesTableSeeder
 */
class SeriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $series_data = File::get("database/data/series.json");
        $series_data = json_decode($series_data, true);
        $created_at = date("Y-m-d H:i:s");

        foreach ($series_data as $series) {
            DB::table('series')->insert([
                'code' => $series['codigo'],
                'name' => '',
                'user_id' => 1,
                'date_start' => $created_at,
                'created_at' => $created_at,
                'updated_at' => $created_at,
            ]);
        }

    }
}
