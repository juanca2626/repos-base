<?php

use Illuminate\Database\Seeder;
use App\SerieHeader;
use Carbon\Carbon;

class SerieHeaderSeeder extends Seeder
{
    public function run()
    {
        SerieHeader::create([
            'name' => 'Perú Facile',
            'year' => Carbon::now()->year,
        ]);
    }
}
