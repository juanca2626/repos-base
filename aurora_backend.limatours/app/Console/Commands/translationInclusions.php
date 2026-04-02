<?php

namespace App\Console\Commands;

use App\Imports\InclusionImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class translationInclusions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:translationInclusions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa traducciones desde un archivo excel';

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
        try {
            Excel::import(new InclusionImport(), storage_path().'/imports/'.'inclusions_translations.xlsx');
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
