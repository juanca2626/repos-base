<?php

namespace App\Console\Commands;

use App\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Stella\StellaService;
use App\FileImportLog;
use App\FileService;
use App\Http\Traits\Files;

class MigrateFilesNonExists extends Command
{
    use Files;

    protected $stella_service;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:migrate_non_exists';

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
        $this->stella_service = new StellaService;
//         $this->files_service = new Files;
    }

    public function throwError($ex)
    {
        return [
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
            'message' => $ex->getMessage(),
            'type' => 'error',
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try
        {
            $min_file = FileImportLog::where('status', '=', 1)->min('file');
            $nroref = ($min_file) ? $min_file : '';

            $array = [
                'nroref' => $nroref,
            ];

            $files = (array) $this->stella_service->migrate_files_non_exists($array);

            foreach($files['data'] as $key => $value)
            {
                $nroref = (int) $value->nroref;
                $file = File::where('file_number', '=', $nroref)->first();
//                print_r($nroref); die;
                if(!$file)
                {
                    $response = $this->import_file($nroref);
                }
                else
                {
                    $response['success'] = true;
                    $response['message'] = 'File already exist';
                }
                // Servicios..
                if($response['success'])
                {
                    $file = File::where('file_number', '=', $nroref)->first();
                    $services_import_ifx = $this->import_services($nroref, $file->id, '');

                    $log = FileImportLog::where('file', '=', $value->nroref)->first();
                    
                    if($log == null OR $log == '')
                    {
                        $log = new FileImportLog;
                    }
                    
                    $log->file = $value->nroref;
                    $log->date = date("Y-m-d H:i:s");
                    $log->status = ($services_import_ifx['success']) ? 1 : 0;
                    $log->message = 'Files | Services - ' . $services_import_ifx['message'];
                    $log->save();
                }
                else
                {
                    $log = FileImportLog::where('file', '=', $value->nroref)->first();
                    
                    if($log == null OR $log == '')
                    {
                        $log = new FileImportLog;
                    }

                    $log->file = $value->nroref;
                    $log->date = date("Y-m-d H:i:s");
                    $log->status = ($response['success']) ? 1 : 0;
                    $log->message = 'Files - ' . $response['message'];
                    $log->save();
                }
            }
        }
        catch(\Exception $ex)
        {
            print_r($this->throwError($ex));
        }
    }
}
