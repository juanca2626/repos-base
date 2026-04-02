<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportHotelHyperguestPullSeeder extends Seeder
{
    public function run()
    {
        $path = 'database/data/sql/import_hyperguest_data.sql';
        DB::transaction(function () use ($path) {
            DB::unprepared(file_get_contents($path));
        });

        $this->command->info('Importación de data estática de hoteles Hyperguest Pull completada');
    }
}
