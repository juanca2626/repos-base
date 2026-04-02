<?php

use App\Translation;
use Illuminate\Database\Seeder;

// @codingStandardsIgnoreLine
class TranslationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        function addData($translations, $module, $lang)
        {
            foreach ($translations as $key => $translation) {
                if (gettype($translation) === "string") {
                    Translation::updateOrCreate(
                        [
                            'type' => 'label',
                            'object_id' => 0,
                            'slug' => $module . '.' . $key,
                            'language_id' => $lang
                        ],
                        ['value' => $translation]
                    );
                } else {
                    addData($translation, $module . '.' . $key, $lang);
                }
            }
        }

        $fi = new FilesystemIterator(
            getcwd() . '/database/data/translations',
            FilesystemIterator::SKIP_DOTS
        );

        $bar = $this->command->getOutput()->createProgressBar(iterator_count($fi));
        $bar->setFormat(' %current%/%max% [%bar%] <info>%percent:3s%%</info> -- %message%');
        $bar->start();

        $bar->setMessage('Starting...');

        if ($handle = opendir(getcwd() . '/database/data/translations')) {
            while (false !== ($fileName = readdir($handle))) {
                $file = getcwd() . '/database/data/translations/' . $fileName;
                $path_parts = pathinfo($file);

                if ($path_parts['extension'] === 'json') {
                    $bar->setMessage('Module: ' . $path_parts['filename']);

                    $translation = json_decode(file_get_contents($file));
                    addData($translation->es, strtolower($path_parts['filename']), 1);
                    addData($translation->en, strtolower($path_parts['filename']), 2);
                }
                $bar->advance();
            }
            closedir($handle);
        }
        $bar->finish();
    }
}
