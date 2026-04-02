<?php

use Illuminate\Database\Seeder;
use App\SerieDeparture;
use App\SerieHeader;

class SerieDepartureSeeder extends Seeder
{
    public function run()
    {
        // Buscar el header dinámicamente (mejor práctica)
        $header = SerieHeader::where('name', 'Perú Facile')->first();

        if (!$header) {
            $this->command->error('SerieHeader "Perú Facile" no existe.');
            return;
        }

        $departures = [
            ['#1', false, null, false],
            ['#2', false, null, false],
            ['#3', false, null, false],
            ['#4', false, null, false],
            ['#5', false, null, false],
            ['#6', true, 'Semana Santa', false],
            ['#7', false, null, false],
            ['#8', false, null, false],
            ['#9', false, null, false],
            ['#10', false, null, false],
            ['#11', false, null, false],
            ['#12', true, 'Inti Raymi', false],
            ['#13', false, null, false],
            ['#14', false, null, false],
            ['#15', false, null, false],
            ['#16', false, null, false],
            ['#17', false, null, false],
            ['#18', false, null, false],
            ['Extra #1', false, null, true],
            ['#19', false, null, false],
            ['Extra #2', false, null, true],
            ['#20', false, null, false],
            ['Extra #3', false, null, true],
            ['#21', false, null, false],
            ['#22', false, null, false],
            ['#23', false, null, false],
            ['#24', false, null, false],
            ['#25', false, null, false],
            ['#26', false, null, false],
            ['#27', false, null, false],
            ['#28', false, null, false],
            ['#29', false, null, false],
            ['#30', false, null, false],
            ['#31', false, null, false],
            ['#32', false, null, false],
            ['#33', false, null, false],
            ['#34', false, null, false],
            ['#35', true, 'Capodanno', false],
        ];

        foreach ($departures as $departure) {
            SerieDeparture::create([
                'serie_header_id' => $header->id,
                'name' => $departure[0],
                'has_holiday' => $departure[1],
                'name_holiday' => $departure[2],
                'has_extra_departure' => $departure[3],
                'link_guidelines' => null,
            ]);
        }
    }
}
