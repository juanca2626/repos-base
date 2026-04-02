<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class forceClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'force_clear:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*
            'storage/framework/cache/',
            'storage/framework/views/',
            'public/images/portadas/create/',
        */

        try {
            $routes = [
                'public/generate-itinerary/'
            ];

            foreach ($routes as $route) {
                $folder = __DIR__ . '/../../../' . $route;

                if (!is_dir($folder)) {
                    continue;
                }

                $dir = opendir($folder);
                while (($f = readdir($dir)) !== false) {
                    if ($f === '.' || $f === '..') continue;

                    $path = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $f;

                    if (!is_dir($path) && (time() - filemtime($path) > 3600 * 2)) {
                        unlink($path);
                    }
                }
                closedir($dir);
            }

            return 1;
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return 0;
        }
    }
}
