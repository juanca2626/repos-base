<?php

namespace App\Console\Commands;

use App\Client;
use App\FileImportLog;
use App\Markup;
use App\Service;
use App\ServiceClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugImportFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:debug_imports';

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
        DB::transaction(function () {

            FileImportLog::select('file')
                ->whereIn('status', [0,1])
                ->distinct()
                ->chunk(50, function ($logs) {
                    foreach ( $logs as $log ){
                        $logs_ = FileImportLog::where('file', $log->file)->orderBy('id', 'desc')->get();
                        for ($i=0; $i<count($logs_); $i++){
                            if($i>0){
                                $logs_[$i]->delete();
                            }
                        }
                    }
                });
        });

    }

}
