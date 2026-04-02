<?php

namespace App\Console\Commands;

use App\GenerateRatesInCalendar;
use App\Hotel;
use Illuminate\Console\Command;
use App\Jobs\RateCalendaries;
use App\Http\Traits\GenerateRatesCalendar;

class UpdateCalendaryByHotel extends Command
{
    use GenerateRatesCalendar;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendary:rate_hotels_update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generamos el calendario de la tarifas';

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
        $hotels = Hotel::with('rates_plans')->where('status','1');
        // $hotels->where('id', 210);
        $hotels = $hotels->orderBy('id')->get();

        foreach($hotels as $hotel){
            foreach($hotel->rates_plans as $rates_plan){

                if($rates_plan->status == "1"){

                    $params = [];
                    $params['hotel_id'] = $hotel->id;
                    $params['rates_plans_id'] = $rates_plan->id;
                    $params['room_id'] = NULL;
                    $params['perido'] = date('Y');
                    $params['status'] = 1;
                    $params['user_add'] = 1;

                    $generated = GenerateRatesInCalendar::where('hotel_id',$params['hotel_id'] )->where('rates_plans_id',$params['rates_plans_id'])->where('status','1')->get();

                    if(count($generated)>0){
                        $params['status'] = 3;
                        $params['status_message'] = 'Hay un proceso ejecutándose, no puede ejecutar esta acción';
                        GenerateRatesInCalendar::create($params); // Ejemplo de ejecusion del job
                    }else{
                        $rangos  = $this->generateRates($params['hotel_id'],$params['rates_plans_id'],$params['room_id'],$params['perido']);

                        if(count($rangos['date_range_hotel_duplicate'])>0){
                            $params['status'] = 3;
                            $params['status_message'] = 'Tiene más de un rango de fechas que duplicaran la tarifa';
                            GenerateRatesInCalendar::create($params); // Ejemplo de ejecusion del job
                        }else{

                            if(count($rangos['rateRoomDate']) == 0){
                                $params['status'] = 3;
                                $params['status_message'] = 'No hay tarifas con rangos de fechas ha procesar';
                                GenerateRatesInCalendar::create($params); // Ejemplo de ejecusion del job
                            }else{
                                $generate_rates_in_calendar = GenerateRatesInCalendar::create($params); // Ejemplo de ejecusion del job
                                // $this->processRates($generate_rates_in_calendar->id);
                                RateCalendaries::dispatch($generate_rates_in_calendar->id);
                            }

                        }

                    }

                }

            }
        }


    }
}
