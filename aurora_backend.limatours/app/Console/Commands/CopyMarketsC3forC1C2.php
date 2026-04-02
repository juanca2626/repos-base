<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use App\Http\Stella\StellaService;
use App\User;
use App\UserMarket;
use Carbon\Carbon;
use Illuminate\Console\Command;


class CopyMarketsC3forC1C2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'markets:copy_c3_for_c1_c2';

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
        $codes_executive = ['pbm','mrs','rlc','cpa','cct',
                            'aoc','fcc','mcj','rdc','dbd',
                            'pab','jka','stp','jap','rmn',
                            'nce','saa','lzz','ltr','llc'];

        $ids_user = User::whereIn('code',$codes_executive)->pluck('id')->toArray();

        foreach ($ids_user as $id){

            UserMarket::insert([
                'user_id'=>$id,
                'market_id'=>5,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
            UserMarket::insert([
                'user_id'=>$id,
                'market_id'=>9,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
    }
}
