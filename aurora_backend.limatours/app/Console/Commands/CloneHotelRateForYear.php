<?php

namespace App\Console\Commands;

use App\CloneLog;
use App\ConfigMarkup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Hotel;
use Illuminate\Support\Facades\DB;
use App\Jobs\RateCalendaries;

class CloneHotelRateForYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotels:clone_hotel_rate_year_for_rate______';

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
        // DB::transaction(function () {

            $year = Carbon::now()->year;
            $year_to = Carbon::now()->year + 1;
            $max = Hotel::max('id');
 
            Hotel::with(['rates_plans'])
                ->where('status',1)
                ->orderBy('hotels.id', 'ASC')
//                ->where('id', 4)
                ->chunk( 5, function($hotels) use ($year, $year_to, $max){

                    foreach ( $hotels as $hotel ){

                        // Traer el markup pendiente..
                        $config_markup = ConfigMarkup::where('type', '=', 2)
                            ->where('category_id', '=', $hotel->typeclass_id) 
                            ->first();

                    
                        foreach ($hotel->rates_plans as $rate_plan) {
                            if( !$rate_plan->promotions and $rate_plan->status ){ // SI NO ES PROMOCIONAL Y RATES ESTA ACTIVO

                                $markup = 1;                                
                                if($rate_plan->flag_process_markup === 1) // le aplicamos el porcentaje solo si tiene activo este el check de margen de proteccion
                                {
                                    if($config_markup != null)
                                    {
                                        $markup = ($config_markup->markup / 100) + 1;                      
                                    }                                    
                                }
                                                                   
                                $date_range_hotels = DB::table('date_range_hotels')
                                    ->where('rate_plan_id', $rate_plan->id)
                                    ->where('date_from', 'LIKE', "%" . $year_to . "%")->get();

                                if ($date_range_hotels->count() === 0) { // SI EL AÑO DESTINO NO TIENE TARIFAS

                                    // Rates año origen usadas para copiar
                                    $date_range_hotels = DB::table('date_range_hotels')
                                        ->where('rate_plan_id', $rate_plan->id)
                                        ->where('date_from', 'LIKE', "%" . $year . "%") 
                                        ->orderBy('group','asc')->get();

                                    if( count($date_range_hotels) > 0 ){
                                        // var_export( json_encode( $date_range_hotels ) ); // Revisar los cortes de 1 a 9 dias

                                        //obtener el maximo valor de grupo de rangos de fechas de tarifa
                                        $max_value_group = 0;
                                        $max_value_group = DB::table('date_range_hotels')
                                                ->where('rate_plan_id', $rate_plan->id)->max('group') + 1;

                                        //Generar nueva data con los rangos de fecha solamente del ano a duplicar
                                        $recent_group = $date_range_hotels[0]->group;
                                        
                                        $periodo = $year_to;
                                        foreach ($date_range_hotels as $date_range_hotel) {
                                        
                                            if ($date_range_hotel->group != $recent_group) {
                                                $recent_group = $date_range_hotel->group;
                                                $max_value_group++;
                                            }
                                            
                                            // dd(Carbon::parse($date_range_hotel->date_from)->addYear()->format('Y-m-d'));
                                            $date_from = Carbon::parse($date_range_hotel->date_from)->addYear()->format('Y-m-d');
                                            $date_to = Carbon::parse($date_range_hotel->date_to)->addYear()->format('Y-m-d');

                                            $year_date_from = Carbon::parse($date_from)->format('Y');
                                            $year_date_to = Carbon::parse($date_to)->format('Y');

                                            if($year_date_from != $year_date_to){
                                                $periodo = $year;
                                            }

                                            DB::table('date_range_hotels')->insert([
                                                'date_from' => $date_from,
                                                'date_to' => $date_to,
                                                'price_adult' => $date_range_hotel->price_adult * $markup,
                                                'price_child' => $date_range_hotel->price_child * $markup,
                                                'price_infant' => $date_range_hotel->price_infant * $markup,
                                                'price_extra' => $date_range_hotel->price_extra * $markup,
                                                'discount_for_national' => $date_range_hotel->discount_for_national,
                                                'rate_plan_id' => $date_range_hotel->rate_plan_id,
                                                'hotel_id' => $date_range_hotel->hotel_id,
                                                'room_id' => $date_range_hotel->room_id,
                                                'rate_plan_room_id' => $date_range_hotel->rate_plan_room_id,
                                                'meal_id' => $date_range_hotel->meal_id,
                                                'policy_id' => $date_range_hotel->policy_id,
                                                'old_id_date_range' => null,
                                                'group' => $max_value_group,
                                                'updated' => 9, // este campo solo lo usaremos para identificar los rangos que se han clonado
                                                'flag_migrate' => 1, // Para saber que es una tarifa que se ha duplicado..
                                                'created_at' => Carbon::now(),
                                                'updated_at' => Carbon::now(),
                                            ]);
                                            
                                        }

                                        // Generamos un registro para que se proceso la tarifa en el calendario
                                        $generate_rates_in_calendar_id = DB::table('generate_rates_in_calendars')->insertGetId([
                                            'hotel_id' => $hotel->id,
                                            'rates_plans_id' => $rate_plan->id,
                                            'perido' => $periodo,
                                            'status' => 0,
                                            'user_add' => 1,
                                            'created_at' => Carbon::now(),
                                            'updated_at' => Carbon::now()
                                        ]);

                                        RateCalendaries::dispatch($generate_rates_in_calendar_id);
                                    }
                                }else{

                                    $generate_rates_in_calendar_id = DB::table('generate_rates_in_calendars')->insertGetId([
                                        'hotel_id' => $hotel->id,
                                        'rates_plans_id' => $rate_plan->id,
                                        'perido' => $year_to,
                                        'status' => 3,
                                        'status_message' => "Ya existe tarifas para el año {$year_to} ",
                                        'user_add' => 1,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now()
                                    ]);

                                }
                             
                                
                            }
                        }

                      
                    }
                });
        // });
    }
}
