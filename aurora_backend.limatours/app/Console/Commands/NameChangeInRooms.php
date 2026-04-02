<?php

namespace App\Console\Commands;

use App\Client; 
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
// use Illuminate\Support\Facades\Input;
use App\Imports\NameChangeInRoomImport;

class NameChangeInRooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'room:name_change_in_rooms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change room names en masse';

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

            $path = 'database/data/files/habitaciones_por_hoteles.xlsx';
            
            Excel::import(new NameChangeInRoomImport, $path);

        } catch (\Exception $e) {
            dd($e);  
        }

    }

     

}
