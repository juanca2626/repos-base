<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class CountryCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'country:code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $countrycode = DB::table('countrycode')->get();
        $data = [];
        foreach($countrycode as $code){
            array_push($data, [
                'country' =>  $code->ISO2,
                'code' =>  $code->E164
            ]);
        }
        echo json_encode($data);
    }
}
