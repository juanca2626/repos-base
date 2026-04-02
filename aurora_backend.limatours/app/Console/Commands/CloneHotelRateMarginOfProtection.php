<?php

namespace App\Console\Commands;

use App\CloneLog;
use App\ConfigMarkup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Hotel;
use App\HotelTypeClass;
use Illuminate\Support\Facades\DB;
use App\Jobs\RateCalendaries;
use App\TypeClass;

class CloneHotelRateMarginOfProtection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotels:clone_hotel_rate_margin_of_protection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clona todas las tarifas activas de los hoteles activos con un margen de proteccion del 5%';

    // ejecutar los jobs : php artisan queue:work clone_hotel_rate_protection --queue=rate_protection

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

        $this->info('Iniciando proceso de clonado de tarifas de hoteles');

        $total_hoteles_procesados = 0;
        $total_hoteles_omitidos = 0;
        $total_tarifas_clonadas = 0;

        Hotel::with(['rates_plans'])
            ->where('status', 1)
            ->orderBy('hotels.id', 'ASC')
            //                ->where('id', 4)
            ->chunk(5, function ($hotels) use ($year, $year_to, &$total_hoteles_procesados, &$total_hoteles_omitidos, &$total_tarifas_clonadas) {

                foreach ($hotels as $hotel) {
                    $hotel_actualizado = false;

                    //1 -> boutique
                    //2 -> lujo
                    //3 -> super lujo
                    //8 -> logde resort
                    $margin_of_protection = 5;
                    $markup = ($margin_of_protection / 100) + 1;

                    /*$type_class_count = HotelTypeClass::where('hotel_id', $hotel->id)->whereIn('typeclass_id', [1, 3])->where('year', $year)->count();
                    if ($type_class_count > 0) {
                        continue;
                    }*/

                    $type_class_count = HotelTypeClass::where('hotel_id', $hotel->id)->whereIn('typeclass_id', [1, 2, 3, 8])->where('year', $year)->count();
                    if ($type_class_count > 0) {
                        $margin_of_protection = 10;
                        $markup = ($margin_of_protection / 100) + 1;
                    }

                    foreach ($hotel->rates_plans as $rate_plan) {
                        $hotel_procesado_bandera = false;
                        if (
                            $rate_plan->channel_id !== 1 ||
                            stripos($rate_plan->name, 'regular') === false
                            /* || strpos($rate_plan->name, 'Rate Guide') !== false ||
                            strpos($rate_plan->name, 'Tarifa promocional') !== false ||
                            strpos($rate_plan->name, 'Tarifa Multidestino') !== false ||
                            strpos($rate_plan->name, 'Tarifa Especial Multipropiedad') !== false ||
                            strpos($rate_plan->name, 'Rate Guide with overnight') !== false ||
                            stripos($rate_plan->name, 'región') !== false ||
                            stripos($rate_plan->name, 'mercado') !== false*/
                        ) {
                            continue;
                        }

                        if (!$rate_plan->promotions and $rate_plan->status) { // SI NO ES PROMOCIONAL Y RATES ESTA ACTIVO

                            $date_range_hotels = DB::table('date_range_hotels')
                                ->where('rate_plan_id', $rate_plan->id)
                                ->where('date_from', 'LIKE', "%" . $year_to . "%")->get();

                            if ($date_range_hotels->count() === 0) { // SI EL AÑO DESTINO NO TIENE TARIFAS

                                // Rates año origen usadas para copiar
                                $date_range_hotels = DB::table('date_range_hotels')
                                    ->where('rate_plan_id', $rate_plan->id)
                                    ->where('date_from', 'LIKE', "%" . $year . "%")
                                    ->where('flag_migrate', 0)
                                    ->whereExists(function ($query) {
                                        $query->select(DB::raw(1))
                                              ->from('rooms')
                                              ->whereColumn('rooms.id', 'date_range_hotels.room_id');
                                    })
                                    ->orderBy('group', 'asc')->get();

                                if (count($date_range_hotels) > 0) {
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

                                        if ($year_date_from != $year_date_to) {
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

                                        $total_tarifas_clonadas++;
                                        $hotel_procesado_bandera = true;
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

                                    $this->info('Hotel actualizado: ' . $hotel->id);
                                    $hotel_actualizado = true;
                                }
                            } else {
                                $periodo = $year_to;
                                $max_value_group = DB::table('date_range_hotels')
                                    ->where('rate_plan_id', $rate_plan->id)
                                    ->max('group') + 1;


                                // Obtener el último período registrado en 2026 para cada tarifa (room_id y rate_plan_room_id)
                                $last_period_2026 = [];
                                $periods_2026 = DB::table('date_range_hotels')
                                    ->where('rate_plan_id', $rate_plan->id)
                                    ->whereYear('date_to', $year_to) // Año destino (2026)
                                    ->select('room_id', 'rate_plan_room_id', DB::raw('MAX(date_to) as last_date_to'))
                                    ->groupBy('room_id', 'rate_plan_room_id')
                                    ->get();

                                // Guardar los datos en un array más comprensible
                                foreach ($periods_2026 as $period) {
                                    $last_period_2026[$period->room_id][$period->rate_plan_room_id] = $period->last_date_to;
                                }

                                $date_range_hotels = DB::table('date_range_hotels')
                                ->where('rate_plan_id', $rate_plan->id)
                                ->where('date_from', 'LIKE', "%" . $year . "%")
                                ->where('flag_migrate', 0)
                                ->whereExists(function ($query) {
                                    $query->select(DB::raw(1))
                                          ->from('rooms')
                                          ->whereColumn('rooms.id', 'date_range_hotels.room_id');
                                })
                                ->orderBy('group', 'asc')->get();

                                if (count($date_range_hotels) > 0) {
                                    $recent_group = $date_range_hotels[0]->group;
                                    foreach ($date_range_hotels as $date_range_hotel) {
                                        if ($date_range_hotel->group != $recent_group) {
                                            $recent_group = $date_range_hotel->group;
                                            $max_value_group++;
                                        }

                                        $date_from = Carbon::parse($date_range_hotel->date_from)->addYear()->format('Y-m-d');
                                        $date_to = Carbon::parse($date_range_hotel->date_to)->addYear()->format('Y-m-d');

                                        // Buscar el último período de 2026 para esta combinación de room_id y rate_plan_room_id
                                        $last_date_2026 = $last_period_2026[$date_range_hotel->room_id][$date_range_hotel->rate_plan_room_id] ?? null;

                                        // Si hay un último período en 2026, solo clonar los períodos que empiecen después de ese
                                        if ($last_date_2026 && $date_from <= $last_date_2026) {
                                            $this->info("Saltando $date_from porque ya está cubierto en ".$year_to);
                                            continue; // Saltar si el período ya está cubierto en 2026
                                        }
                                        // Verificar si el registro ya existe antes de insertarlo
                                        $exists = DB::table('date_range_hotels')
                                        ->where('date_from', $date_from)
                                        ->where('date_to', $date_to)
                                        ->where('rate_plan_id', $date_range_hotel->rate_plan_id)
                                        ->where('hotel_id', $date_range_hotel->hotel_id)
                                        ->where('room_id', $date_range_hotel->room_id)
                                        ->where('rate_plan_room_id', $date_range_hotel->rate_plan_room_id)
                                        ->exists();

                                        if ($exists) {
                                            $this->info("El registro ya existe para $date_from, saltando...");
                                            continue; // Evitar insertar duplicados
                                        }
                                        $this->info("Registrando: $date_from para el hotel {$hotel->id}");
                                        // Insertar solo los períodos que faltan en 2026
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
                                            'updated' => 9,
                                            'flag_migrate' => 1,
                                            'created_at' => Carbon::now(),
                                            'updated_at' => Carbon::now(),
                                        ]);

                                        $total_tarifas_clonadas++;
                                        $hotel_procesado_bandera = true;
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

                                    $this->info('Hotel actualizado: ' . $hotel->id);
                                    $hotel_actualizado = true;
                                }else{
                                    /*$generate_rates_in_calendar_id = DB::table('generate_rates_in_calendars')->insertGetId([
                                        'hotel_id' => $hotel->id,
                                        'rates_plans_id' => $rate_plan->id,
                                        'perido' => $year_to,
                                        'status' => 3,
                                        'status_message' => "Ya existe tarifas para el año {$year_to} ",
                                        'user_add' => 1,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now()
                                    ]);*/
                                }
                            }
                        }
                    }

                    if ($hotel_actualizado) {
                        $total_hoteles_procesados++;
                    } else {
                        $total_hoteles_omitidos++;
                    }
                }
            });

        $this->info('--------------- RESUMEN DE EJECUCIÓN ---------------');
        $this->info("Total hoteles con clonación procesada: {$total_hoteles_procesados}");
        $this->info("Total hoteles omitidos (ya existía protección): {$total_hoteles_omitidos}");
        $this->info("Total de rangos de tarifas insertados: {$total_tarifas_clonadas}");
        $this->info('----------------------------------------------------');
    }
}
