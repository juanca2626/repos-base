<?php

use Illuminate\Database\Seeder;
use App\SerieDepartureProgram;

class SerieDepartureProgramSeeder extends Seeder
{
    public function run()
    {
        $year = 2026;

        /**
         * Estructura:
         * departure_id => [
         *   program_id => 'YYYY-MM-DD'
         * ]
         *
         * Program IDs:
         * 1 = Misterioso
         * 2 = Classico
         * 3 = Express
         * 4 = Mistico
         * 5 = Imperiale / Peru-Bolivia
         */
        $data = [

            // Salida #1 .. #18  => departure_id 1..18
            1 => [ 1 => "$year-01-12", 2 => "$year-01-15", 3 => "$year-01-16", 4 => "$year-01-19", 5 => "$year-01-19" ],
            2 => [ 1 => "$year-01-26", 2 => "$year-01-29", 3 => "$year-01-30", 4 => "$year-02-02", 5 => "$year-02-02" ],
            3 => [ 1 => "$year-02-09", 2 => "$year-02-12", 3 => "$year-02-13", 4 => "$year-02-16", 5 => "$year-02-16" ],
            4 => [ 1 => "$year-02-23", 2 => "$year-02-26", 3 => "$year-02-27", 4 => "$year-03-02", 5 => "$year-03-02" ],
            5 => [ 1 => "$year-03-09", 2 => "$year-03-12", 3 => "$year-03-13", 4 => "$year-03-16", 5 => "$year-03-16" ],
            6 => [ 1 => "$year-03-23", 2 => "$year-03-26", 3 => "$year-03-27", 4 => "$year-03-30", 5 => "$year-03-30" ],
            7 => [ 1 => "$year-04-06", 2 => "$year-04-09", 3 => "$year-04-10", 4 => "$year-04-13", 5 => "$year-04-13" ],
            8 => [ 1 => "$year-04-20", 2 => "$year-04-23", 3 => "$year-04-24", 4 => "$year-04-27", 5 => "$year-04-27" ],
            9 => [ 1 => "$year-05-04", 2 => "$year-05-07", 3 => "$year-05-08", 4 => "$year-05-11", 5 => "$year-05-11" ],
            10 => [ 1 => "$year-05-18", 2 => "$year-05-21", 3 => "$year-05-22", 4 => "$year-05-25", 5 => "$year-05-25" ],
            11 => [ 1 => "$year-06-01", 2 => "$year-06-04", 3 => "$year-06-05", 4 => "$year-06-08", 5 => "$year-06-08" ],
            12 => [ 1 => "$year-06-14", 2 => "$year-06-17", 3 => "$year-06-18", 4 => "$year-06-21", 5 => "$year-06-21" ],
            13 => [ 1 => "$year-06-22", 2 => "$year-06-25", 3 => "$year-06-26", 4 => "$year-06-29", 5 => "$year-06-29" ],
            14 => [ 1 => "$year-06-29", 2 => "$year-07-02", 3 => "$year-07-03", 4 => "$year-07-06", 5 => "$year-07-06" ],
            15 => [ 1 => "$year-07-06", 2 => "$year-07-09", 3 => "$year-07-10", 4 => "$year-07-13", 5 => "$year-07-13" ],
            16 => [ 1 => "$year-07-13", 2 => "$year-07-16", 3 => "$year-07-17", 4 => "$year-07-20", 5 => "$year-07-20" ],
            17 => [ 1 => "$year-07-20", 2 => "$year-07-23", 3 => "$year-07-24", 4 => "$year-07-27", 5 => "$year-07-27" ],
            18 => [ 1 => "$year-07-27", 2 => "$year-07-30", 3 => "$year-07-31", 4 => "$year-08-03", 5 => "$year-08-03" ],

            // Extra #1 => departure_id 19
            19 => [ 1 => "$year-07-31", 2 => "$year-08-03", 3 => "$year-08-04", 4 => "$year-08-07", 5 => "$year-08-07" ],

            // Salida #19 => departure_id 20
            20 => [ 1 => "$year-08-03", 2 => "$year-08-06", 3 => "$year-08-07", 4 => "$year-08-10", 5 => "$year-08-10" ],

            // Extra #2 => departure_id 21
            21 => [ 1 => "$year-08-07", 2 => "$year-08-10", 3 => "$year-08-11", 4 => "$year-08-14", 5 => "$year-08-14" ],

            // Salida #20 => departure_id 22
            22 => [ 1 => "$year-08-10", 2 => "$year-08-13", 3 => "$year-08-14", 4 => "$year-08-17", 5 => "$year-08-17" ],

            // Extra #3 => departure_id 23
            23 => [ 1 => "$year-08-14", 2 => "$year-08-17", 3 => "$year-08-18", 4 => "$year-08-21", 5 => "$year-08-21" ],

            // Salida #21 .. #35 => departure_id 24..38
            24 => [ 1 => "$year-08-17", 2 => "$year-08-20", 3 => "$year-08-21", 4 => "$year-08-24", 5 => "$year-08-24" ], // #21
            25 => [ 1 => "$year-08-24", 2 => "$year-08-27", 3 => "$year-08-28", 4 => "$year-08-31", 5 => "$year-08-31" ], // #22
            26 => [ 1 => "$year-08-31", 2 => "$year-09-03", 3 => "$year-09-04", 4 => "$year-09-07", 5 => "$year-09-07" ], // #23
            27 => [ 1 => "$year-09-07", 2 => "$year-09-10", 3 => "$year-09-11", 4 => "$year-09-14", 5 => "$year-09-14" ], // #24
            28 => [ 1 => "$year-09-14", 2 => "$year-09-17", 3 => "$year-09-18", 4 => "$year-09-21", 5 => "$year-09-21" ], // #25
            29 => [ 1 => "$year-09-21", 2 => "$year-09-24", 3 => "$year-09-25", 4 => "$year-09-28", 5 => "$year-09-28" ], // #26
            30 => [ 1 => "$year-09-28", 2 => "$year-10-01", 3 => "$year-10-02", 4 => "$year-10-05", 5 => "$year-10-05" ], // #27
            31 => [ 1 => "$year-10-05", 2 => "$year-10-08", 3 => "$year-10-09", 4 => "$year-10-12", 5 => "$year-10-12" ], // #28
            32 => [ 1 => "$year-10-19", 2 => "$year-10-22", 3 => "$year-10-23", 4 => "$year-10-26", 5 => "$year-10-26" ], // #29
            33 => [ 1 => "$year-11-02", 2 => "$year-11-05", 3 => "$year-11-06", 4 => "$year-11-09", 5 => "$year-11-09" ], // #30
            34 => [ 1 => "$year-11-16", 2 => "$year-11-19", 3 => "$year-11-20", 4 => "$year-11-23", 5 => "$year-11-23" ], // #31
            35 => [ 1 => "$year-11-30", 2 => "$year-12-03", 3 => "$year-12-04", 4 => "$year-12-07", 5 => "$year-12-07" ], // #32
            36 => [ 1 => "$year-12-07", 2 => "$year-12-10", 3 => "$year-12-11", 4 => "$year-12-14", 5 => "$year-12-14" ], // #33
            37 => [ 1 => "$year-12-14", 2 => "$year-12-17", 3 => "$year-12-18", 4 => "$year-12-21", 5 => "$year-12-21" ], // #34
            38 => [ 1 => "$year-12-23", 2 => "$year-12-26", 3 => "$year-12-27", 4 => "$year-12-30", 5 => "$year-12-30" ], // #35
        ];

        foreach ($data as $departureId => $programs) {
            foreach ($programs as $programId => $date) {
                // Evita duplicados y permite re-ejecutar el seeder
                SerieDepartureProgram::updateOrCreate(
                    [
                        'serie_program_id' => $programId,
                        'serie_departure_id' => $departureId,
                    ],
                    [
                        'date' => $date,
                    ]
                );
            }
        }

        $this->command->info('SerieDepartureProgramSeeder OK (2026).');
    }
}
