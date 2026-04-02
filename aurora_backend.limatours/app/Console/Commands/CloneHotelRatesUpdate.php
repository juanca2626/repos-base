<?php

namespace App\Console\Commands;

use App\CloneLog;
use App\ConfigMarkup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Hotel;
use Illuminate\Support\Facades\DB;

class CloneHotelRatesUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotels:clone_rates_update';

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

            $config_markups = ConfigMarkup::where('type', '=', 2)
                ->where('percent', '=', 0)
                ->where('status', '=', 1)->orderBy('id', 'desc')->pluck('category_id');

            Hotel::with(['rates_plans' => function ($query) {
                $query->where('flag_process_markup', '=', 1);
            }])
                ->whereIn('typeclass_id', $config_markups)
                ->orderBy('hotels.id', 'ASC')
                ->whereHas('rates_plans', function ($query) {
                    $query->where('flag_process_markup', '=', 1);
                })
//                ->where('id', 4)
                ->chunk( 5, function($hotels) use ($year, $year_to, $config_markups) {

                    if(count($hotels) == 0)
                    {
                        ConfigMarkup::where('type', '=', 2)
                            ->whereIn('category_id', '=', $config_markups)
                            ->where('status', '=', 1)->orderBy('id', 'desc')->update([
                                'percent' => 100,
                                'status' => 2
                            ]);
                    }
                    
                    foreach ( $hotels as $hotel )
                    {
                        $max = Hotel::where('typeclass_id', '=', $hotel->typeclass_id)
                            ->whereHas('rates_plans', function ($query) {
                                $query->where('flag_process_markup', '=', 1);
                            })->max('id');

                        // Traer el markup pendiente..
                        $config_markup = ConfigMarkup::where('type', '=', 2)
                            ->where('category_id', '=', $hotel->typeclass_id)
                            ->where('status', '=', 1)->orderBy('id', 'desc')->first();

                        $markup = 1; $markup_prev = 0; $process = FALSE;

                        if($config_markup != null)
                        {
                            $markup = ($config_markup->markup / 100) + 1;
                            $markup_prev = ($config_markup->prev / 100);
                        }

                        foreach ($hotel->rates_plans as $rate_plan)
                        {
                            if( !($rate_plan->promotions) )
                            { // SI NO ES PROMOCIONAL

                                // En caso se ignore los margenes de protección para la tarifa asociada
                                if((int) $rate_plan->flag_process_markup === 1) // si es 0 o nulo ignora
                                {
                                    $process = TRUE;
                                }

                                if($process)
                                {

                                    $has_migrate = DB::table('date_range_hotels')
                                    ->where('rate_plan_id', $rate_plan->id)
                                    ->where('flag_migrate', '<>', 1)
                                    ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                    // ->whereNull('deleted_at')
                                    ->orderBy('created_at', 'desc')->count();

                                    if($has_migrate == 0)
                                    {
                                        // Eliminar las tarifas en caso todas las que existan sean duplicadas..
                                        DB::table('date_range_hotels')
                                            ->where('rate_plan_id', $rate_plan->id)
                                            ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                            ->delete();
                                    }

                                    $date_range_hotels = DB::table('date_range_hotels')
                                        ->where('rate_plan_id', $rate_plan->id)
                                        ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                        ->where(function ($query) {
                                            $query->orWhere('flag_migrate', '=', 1);
                                            $query->orWhereNull('flag_migrate');
                                        })
                                        // ->whereNull('deleted_at')
                                        ->orderBy('created_at', 'desc')->get();

                                    if ($date_range_hotels->count() === 0)
                                    {
                                        // SI EL AÑO DESTINO NO TIENE TARIFAS

                                        // Rates año origen usadas para copiar
                                        $date_range_hotels = DB::table('date_range_hotels')
                                            ->where('rate_plan_id', $rate_plan->id)
                                            ->where('date_from', 'LIKE', "%" . $year . "%")
                                            // ->whereNull('deleted_at')
                                            ->orderBy('group','asc')->get();

                                        if( count($date_range_hotels) > 0 )
                                        {
                                            // var_export( json_encode( $date_range_hotels ) ); // Revisar los cortes de 1 a 9 dias

                                            //obtener el maximo valor de grupo de rangos de fechas de tarifa
                                            $max_value_group = 0;
                                            $max_value_group = DB::table('date_range_hotels')
                                                    ->where('rate_plan_id', $rate_plan->id)->max('group') + 1;

                                            //Generar nueva data con los rangos de fecha solamente del ano a duplicar
                                            $recent_group = $date_range_hotels[0]->group;

                                            foreach ($date_range_hotels as $date_range_hotel) {

                                                $date_range_hotel_date_from = Carbon::parse( $date_range_hotel->date_from );
                                                $date_range_hotel_date_to = Carbon::parse( $date_range_hotel->date_to );

                                                $diff_dates = $date_range_hotel_date_from->diffInDays($date_range_hotel_date_to);

                                                if( $diff_dates > 9 ){

                                                    if ($date_range_hotel->group != $recent_group) {
                                                        $recent_group = $date_range_hotel->group;
                                                        $max_value_group++;
                                                    }

                                                    CloneLog::where('type', '=', 'hotel')
                                                    ->where('category_id', '=', $hotel->typeclass_id)
                                                    ->where('item_id', '=', $hotel->id)
                                                    ->where('item_rate_plan_id', '=', $date_range_hotel->rate_plan_id)
                                                    ->where('status', '=', 1)
                                                    ->update([
                                                        'status' => 0
                                                    ]);

                                                    // Generando log..
                                                    $log = new CloneLog;
                                                    $log->type = 'hotel'; // Hotel
                                                    $log->category_id = $hotel->typeclass_id;
                                                    $log->item_id = $hotel->id;
                                                    $log->item_rate_plan_id = $date_range_hotel->rate_plan_id;
                                                    $log->date = Carbon::now();
                                                    $log->markup = $markup;
                                                    $log->status = 1;
                                                    $log->save();

                                                    if($markup_prev > 0)
                                                    {
                                                        $date_range_hotel->price_adult = $date_range_hotel->price_adult - ($date_range_hotel->price_adult * $markup_prev);
                                                        $date_range_hotel->price_child = $date_range_hotel->price_child - ($date_range_hotel->price_child * $markup_prev);
                                                        $date_range_hotel->price_infant = $date_range_hotel->price_infant - ($date_range_hotel->price_infant * $markup_prev);
                                                        $date_range_hotel->price_extra = $date_range_hotel->price_extra - ($date_range_hotel->price_extra * $markup_prev);
                                                    }

                                                    DB::table('date_range_hotels')->insert([
                                                        'date_from' => str_replace($year, $year_to, $date_range_hotel->date_from),
                                                        'date_to' => str_replace($year, $year_to, $date_range_hotel->date_to),
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
                                                        'updated' => 1,
                                                        'flag_migrate' => 1, // Para saber que es una tarifa que se ha duplicado..
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now(),
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if($config_markup != null)
                        {
                            $percent = number_format($hotel->id / $max * 100, 2);

                            $config_markup->percent = $percent;
                            $config_markup->save();

                            if($hotel->id == $max)
                            {
                                $config_markup->percent = 100;
                                $config_markup->status = 2;
                                $config_markup->save();

                                /*
                                ConfigMarkup::where('type', '=', 2)
                                    ->where('percent', '>', 0)
                                    ->where('status', '=', 1)->update([
                                        'status' => 2,
                                        'percent' => 100
                                    ]);
                                */
                            }
                        }

                        print_r($max);
                    }
                });
        // });
    }
}
