<?php

use App\Markup;
use Illuminate\Database\Seeder;

class UpdateMarkupsBusinessRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Markup::query()->update(['business_region_id' => 1]);

        $this->command->info('Asignación de BusinessRegion a Markup completada.');
    }
}
