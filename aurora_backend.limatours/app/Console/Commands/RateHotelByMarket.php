<?php

namespace App\Console\Commands;

use App\Hotel;
use App\RatePlanAssociation;
use App\RatesPlans;
use Illuminate\Console\Command;

class RateHotelByMarket extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotel:ratemarket {market_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Agrega el mercado a las tarifas asociadas del hotel';

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
        $marketId = (int) $this->argument('market_id');

        $hotels = Hotel::where('status', 1)->get();

        $h = 0;
        foreach ($hotels as $hotel) {
            $hotel_id = $hotel->id;
            $rate_plans = RatesPlans::where('hotel_id', $hotel_id)->where('rates_plans_type_id', 2)->get(); //2 = Regular

            foreach ($rate_plans as $rate_plan) {
                //Verificar que tiene mercados relacionados
                $rate_plan_asociations = RatePlanAssociation::where('rate_plan_id', $rate_plan->id)->where('entity', 'Market')->get();

                if (count($rate_plan_asociations) > 0) {
                    $association = RatePlanAssociation::where('rate_plan_id', $rate_plan->id)
                        ->where('entity', 'Market')
                        ->where('object_id', $marketId)
                        ->first();

                    if (!$association) {
                        // El registro no existe, crear uno nuevo
                        $association = new RatePlanAssociation();
                        $association->rate_plan_id = $rate_plan->id;
                        $association->entity = 'Market';
                        $association->object_id = $marketId;
                        $association->except = 0;
                        $association->save();                       
                        
                        $this->info('Hotel actualizado: ' . $hotel_id);
                        $h++;
                    }
                }
            }
            
        }


        $this->info('Total Hoteles actualizados: ' . $h . ' Para el mercado: ' . $marketId);
    }
}
