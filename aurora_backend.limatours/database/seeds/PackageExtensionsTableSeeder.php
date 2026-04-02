<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageExtensionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = 'database/data/sql/package_extensions.sql';
        DB::transaction(function () use ($path) {
            DB::unprepared(file_get_contents($path));
        });
    }
}
