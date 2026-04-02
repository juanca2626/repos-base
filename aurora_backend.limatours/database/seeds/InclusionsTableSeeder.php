<?php

use App\Inclusion;
use App\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InclusionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_inclusions = File::get("database/data/inclusions.json");
        $inclusions = json_decode($file_inclusions, true);
        $created_at = date("Y-m-d H:i:s");
        DB::transaction(function () use ($inclusions, $created_at) {
            foreach ($inclusions as $inclusion) {
                $newInclusion = new Inclusion();
                $newInclusion->id = $inclusion['id'];
                $newInclusion->created_at = $created_at;
                $newInclusion->updated_at = $created_at;
                $newInclusion->save();
            }
        });

        $file_inclusions = File::get("database/data/inclusion_translations.json");
        $inclusions = json_decode($file_inclusions, true);
        $created_at = date("Y-m-d H:i:s");
        DB::transaction(function () use ($inclusions, $created_at) {
            foreach ($inclusions as $inclusion) {
                $newTranslation = new Translation();
                $newTranslation->type = $inclusion['type'];
                $newTranslation->object_id = $inclusion['object_id'];
                $newTranslation->slug = $inclusion['slug'];
                $newTranslation->value = $inclusion['value'];
                $newTranslation->language_id = $inclusion['language_id'];
                $newTranslation->created_at = $created_at;
                $newTranslation->updated_at = $created_at;
                $newTranslation->save();
            }
        });
    }
}
