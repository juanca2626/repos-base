<?php

namespace App\Console\Commands;
 
use App\ServiceRatePlan; 
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteServiceRepeat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:delete_repeat {year?}';

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
        // sacamos solo lo servicios y service_rate que tienen tarifas repetidas
        $year =  (int)$this->argument('year');;
        $query = "
        
            SELECT services.id, services.name,service_rate_id, COUNT(*)  
            FROM `service_rate_plans` LEFT JOIN `service_rates` ON  service_rate_plans.`service_rate_id` = service_rates.id
            LEFT JOIN services ON  service_rates.`service_id` = services.id
            WHERE YEAR(date_from) BETWEEN '".$year."' AND '".$year."' AND service_rate_plans.deleted_at IS NULL  
            GROUP BY service_rate_id, date_from, date_to ,pax_from,  pax_to,  price_adult,  price_child
            HAVING	COUNT(*)>1;        

        ";
        $rate_plans = DB::select($query);

        // agrupamos para que solo nos quede un estructura de servicio_id y service_rate_id
        $serviceRates = [];
        foreach($rate_plans as $rate_plan){
            $serviceRates[$rate_plan->id][$rate_plan->service_rate_id] = $rate_plan;
        }
        
        //guardamos una copia de los servicios que se pasaran a eliminar los repetidos en storage/app/DeleteServiceRepeat
        \Storage::disk('local')->put("DeleteServiceRepeat/".date('Y_m_d-H_i_s_m_').$year.".txt", json_encode($serviceRates));

        foreach($serviceRates as $service_id => $serviRates){            
            foreach($serviRates as $service_rate_id => $rows ){
                //agrupamos por date_from, date_to, pax_from, pax_to, esto para agupar todos los repetidos para luego eliminarlos
                $rates_plans_repeat = [];
                $listRatePlans = $this->getRatePlans($year,$service_rate_id);                
                foreach($listRatePlans as $listRatePlan){                    
                    $rates_plans_repeat[$listRatePlan->date_from."|".$listRatePlan->date_to."|".$listRatePlan->pax_from."|".$listRatePlan->pax_to][] = $listRatePlan;                   
                }

                //recorremos el agrupamiento para eliminar las tarifas repetidas apartir del segundo item
                foreach($rates_plans_repeat as $id_ge => $listRates){

                    //ordenamos el listado de tarifas repetidos par que el ultimo en ingresar se queda y el resto lo eliminamos
                    array_multisort(array_column($listRates, 'id'), SORT_DESC, $listRates);                 
                    foreach($listRates as $index => $rates){
                        if($index>0){
                            ServiceRatePlan::find($rates->id)->delete();                                                        
                        }
                    }

                }
         
            }

        }

        // dd($serviceRates);
     
    }
    // nos trae un listado de tarifas por año y service_rate
    public function getRatePlans($year,$service_rate_id){

        $query = "          
            SELECT id,service_rate_id, date_from, date_to ,pax_from,  pax_to,  price_adult,  price_child,  price_infant,  price_guide,`status`, `created_at`,`updated_at`   FROM `service_rate_plans` 
            WHERE service_rate_id = ".$service_rate_id." AND YEAR(date_from) BETWEEN '".$year."' AND '".$year."' AND deleted_at IS NULL  ORDER BY pax_from, date_from;                 
        ";
        $rate_plans = DB::select($query);

        return $rate_plans;
    }

}
