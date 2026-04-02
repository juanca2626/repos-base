<?php

namespace App\Console\Commands;

use App\RatesHistory;
use App\RatesPlans;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class deleteRateHistoriesHotel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:rate_histories_hotels';

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
        $rate_plans = RatesPlans::all();

        foreach ($rate_plans as $rate_plan)
        {
            $rate_history = RatesHistory::where('rates_plan_id',$rate_plan["id"])->orderBy('created_at','desc')->first();
            $rate_history_copy = DB::table('rates_histories_copy')->where('rates_plan_id',$rate_plan["id"])->orderBy('created_at','desc')->first();
            if ($rate_history!=null)
            {
                RatesHistory::where('rates_plan_id',$rate_plan["id"])->where('id','!=',$rate_history->id)->delete();
            }
            if ($rate_history_copy != null)
            {
                DB::table('rates_histories_copy')->where('rates_plan_id',$rate_plan["id"])->where('id','!=',$rate_history_copy->id)->delete();
            }
        }
    }
}
