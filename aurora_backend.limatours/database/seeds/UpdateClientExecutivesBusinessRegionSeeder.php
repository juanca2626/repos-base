<?php

use App\ClientExecutive;
use Illuminate\Database\Seeder;

class UpdateClientExecutivesBusinessRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ClientExecutive::query()->update(['business_region_id' => 1]);

        $this->command->info('Asignación de BusinessRegion a ClientExecutive completada.');
    }
}
