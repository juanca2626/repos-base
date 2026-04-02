<?php

namespace App\Console\Commands;

use App\RatesPlans;
use App\RatesPlansPromotions;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DisableOrEnablePromotionRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rates:disable_or_enable_promotions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disabled or Enabled Promotions Rates';

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
        $today = Carbon::now()->format('Y-m-d');
        $rate_plans  = RatesPlans::select('id','status')->where('promotions',1)->get();

        foreach ($rate_plans as $rate_plan){

            $rate_promotions = RatesPlansPromotions::select('rates_plans_id','promotion_from','promotion_to')
                ->where('rates_plans_id',$rate_plan["id"])
                // ->where('promotion_from','<=',$today)
                ->where('promotion_to','>=',$today)->get();

            if ($rate_promotions->count()>0)
            {
                $rate_plan->status = 1;
                $rate_plan->save();
            }
            else
            {
                $rate_plan->status = 0;
                $rate_plan->save();
            }
        }
    }
}
