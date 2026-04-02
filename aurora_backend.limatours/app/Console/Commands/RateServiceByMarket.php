<?php

namespace App\Console\Commands;

use App\Service;
use App\ServiceRate;
use App\ServiceRateAssociation;
use Illuminate\Console\Command;

class RateServiceByMarket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:ratemarket {market_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Agrega el mercado a las tarifas asociadas del servicio';

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

        $services = Service::where('status', 1)->get();

        $s = 0;
        foreach ($services as $service) {
            $service_id = $service->id;
            $rate_plans = ServiceRate::where('service_id', $service_id)->where('service_type_rate_id', 1)->get(); //1 = Tarifa 1
            
            foreach ($rate_plans as $rate_plan) {
                //Verificar que tiene mercados relacionados
                $rate_plan_asociations = ServiceRateAssociation::where('service_rate_id', $rate_plan->id)->where('entity', 'Market')->get();

                if (count($rate_plan_asociations) > 0) {
                    $association = ServiceRateAssociation::where('service_rate_id', $rate_plan->id)
                        ->where('entity', 'Market')
                        ->where('object_id', $marketId)
                        ->first();

                    if (!$association) {
                        // El registro no existe, crear uno nuevo
                        $association = new ServiceRateAssociation();
                        $association->service_rate_id = $rate_plan->id;
                        $association->entity = 'Market';
                        $association->object_id = $marketId;
                        $association->except = 0;
                        $association->save();                       
                        
                        $this->info('service actualizado: ' . $service_id);
                        $s++;
                    }
                    
                }
            }

            
        }

        $this->info('Total Services actualizados: ' . $s . ' Para el mercado: ' . $marketId);
    }
}
