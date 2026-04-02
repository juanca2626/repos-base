<?php

use App\TagGroup;
use App\Translation;
use Illuminate\Database\Seeder;

class TagGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(TagGroup::class)->times(2)->create();

        $file_tag_translations = File::get("database/data/tag_groups_translations.json");
        $tag_translations = json_decode($file_tag_translations, true);
        foreach ($tag_translations as $tag_translation) {
            Translation::insert($tag_translation);
        }
    }
}
