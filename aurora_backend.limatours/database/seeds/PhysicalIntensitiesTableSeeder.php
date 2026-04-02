<?php

use App\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class PhysicalIntensitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $physical_intensities = [
            [
                'order' => 1,
                'color' => 'FFFF00',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'order' => 2,
                'color' => 'FF7F00',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'order' => 3,
                'color' => 'FF0000',
                'created_at' => date('Y-m-d H:m:s')
            ]
        ];
        DB::table('physical_intensities')->insert($physical_intensities);

        $file_physical_intensity_translations = File::get("database/data/physical_intensity_translations.json");
        $physical_intensity_translations = json_decode($file_physical_intensity_translations, true);
        foreach ($physical_intensity_translations as $physical_intensity_translation) {
            Translation::insert($physical_intensity_translation);
        }

    }
}
