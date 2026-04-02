<?php

use Illuminate\Database\Seeder;
use App\SerieProgram;

class SerieProgramSeeder extends Seeder
{
    public function run()
    {
        $programs = [
            'Misterioso',
            'Classico',
            'Express',
            'Mistico',
            'Imperiale / Peru-Bolivia',
        ];

        foreach ($programs as $program) {
            SerieProgram::create([
                'name' => $program,
            ]);
        }
    }
}
