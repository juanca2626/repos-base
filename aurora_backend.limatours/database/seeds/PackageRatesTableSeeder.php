<?php

use App\PackageRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageRatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = 'database/data/sql/package_rates.sql';
        DB::transaction(function () use ($path) {
            DB::unprepared(file_get_contents($path));
        });
    }
}
