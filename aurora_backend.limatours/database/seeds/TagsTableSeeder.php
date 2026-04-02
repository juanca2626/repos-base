<?php

use App\Tag;
use App\Translation;
use Illuminate\Database\Seeder;

// @codingStandardsIgnoreLine
class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Tag::class)->times(22)->create();

        $file_tag_translations = File::get("database/data/tag_translations.json");
        $tag_translations = json_decode($file_tag_translations, true);
        foreach ($tag_translations as $tag_translation) {
            Translation::insert($tag_translation);
        }
    }
}
