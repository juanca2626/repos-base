<?php

use App\Language;
use App\ServiceType;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;

class TypeServicesTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = Language::select('id')->get();
        $typeServices = [
            [
                'code' => 'SIM',
                'abbreviation' => 'SIM',
            ],
            [
                'code' => 'PC',
                'abbreviation' => 'PC',
            ],
            [
                'code' => 'NA',
                'abbreviation' => 'NA',
            ]
        ];

        foreach ($typeServices as $typeService) {
            ServiceType::create($typeService);
        }

        foreach ($languages as $language) {
            $translations[$language->id] = ['id' => '', 'servicetype_name' => 'COMPARTIDO'];
        }

        $this->saveTranslation($translations, 'servicetype', 1);

        foreach ($languages as $language) {
            $translations[$language->id] = ['id' => '', 'servicetype_name' => 'PRIVADO'];
        }
        $this->saveTranslation($translations, 'servicetype', 2);

        foreach ($languages as $language) {
            $translations[$language->id] = ['id' => '', 'servicetype_name' => 'NINGUNO'];
        }
        $this->saveTranslation($translations, 'servicetype', 3);
    }
}
