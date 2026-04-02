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

class CloneHotelRateMarginOfProtectionSingle extends Command
{
   
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotels:clone_hotel_rate_margin_of_protection_single 
                        {hotel_code : Código del hotel en channel_hotel (ej: URUHHU)}
                        {--margin=5 : Margen de protección en porcentaje (ej: 5)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clona todas las tarifas activas de un hotel específico (buscado por código) con un margen de proteccion del 5%'; /**
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

        $hotel_code = $this->argument('hotel_code');
        $year = Carbon::now()->year;
        $year_to = Carbon::now()->year + 1;

        $this->info("Buscando hotel con código: {$hotel_code}");

        // Obtener el hotel_id desde channel_hotel
        $channel_hotel = DB::table('channel_hotel')
            ->where('code', $hotel_code)
            ->where('channel_id', 1)
            ->first();

        if (!$channel_hotel) {
            $this->error("No se encontró un hotel con código '{$hotel_code}' en channel_id 1");
            return;
        }

        $hotel_id = $channel_hotel->hotel_id;
        $this->info("Hotel encontrado - ID: {$hotel_id}");

        $this->info("Iniciando proceso de clonado de tarifas para el hotel: {$hotel_code}");

        $hotel = Hotel::with(['rates_plans'])
            ->where('status', 1)
            ->where('id', $hotel_id)
            ->first();    

        if (!$hotel) {
            $this->error("Hotel no encontrado o no está activo");
            return;
        }

        //1 -> boutique
        //2 -> lujo   
        //3 -> super lujo
        //8 -> logde resort
        $margin_of_protection = (float) $this->option('margin');
        $markup = ($margin_of_protection / 100) + 1;

        $type_class_count = HotelTypeClass::where('hotel_id', $hotel->id)
            ->whereIn('typeclass_id', [1, 3])
            ->where('year', $year)
            ->count();

        if ($type_class_count > 0) {
            //$this->info("Hotel {$hotel->id} es boutique o super lujo, no se aplica margen de protección");
            //return;
        }

        $type_class_count = HotelTypeClass::where('hotel_id', $hotel->id)
            ->whereIn('typeclass_id', [2])
            ->where('year', $year)
            ->count();

        if ($type_class_count > 0) {
            //$margin_of_protection = 10;
            //$markup = ($margin_of_protection / 100) + 1;
            //$this->info("Aplicando margen de protección del 10% para hotel de lujo");
        }

        foreach ($hotel->rates_plans as $rate_plan) {
            if (
                $rate_plan->channel_id !== 1 ||
                strpos($rate_plan->name, 'Rate Guide') !== false ||
                strpos($rate_plan->name, 'Tarifa promocional') !== false ||
                strpos($rate_plan->name, 'Tarifa Multidestino') !== false ||
                strpos($rate_plan->name, 'Tarifa Especial Multipropiedad') !== false ||
                strpos($rate_plan->name, 'Rate Guide with overnight') !== false ||
                stripos($rate_plan->name, 'región') !== false ||
                stripos($rate_plan->name, 'mercado') !== false
            ) {
                $this->info("Saltando tarifa {$rate_plan->name} (ID: {$rate_plan->id}) - No cumple criterios");
                continue;
            }

            if (!$rate_plan->promotions && $rate_plan->status) {
                $this->info("Procesando tarifa {$rate_plan->name} (ID: {$rate_plan->id})");

                // Paso 1: Eliminar todas las tarifas existentes para el año destino
                DB::table('date_range_hotels')
                    ->where('rate_plan_id', $rate_plan->id)
                    ->where('date_from', 'LIKE', "%" . $year_to . "%")
                    ->delete();

                $this->info("Tarifas existentes para {$year_to} eliminadas");

                // Paso 2: Obtener tarifas del año actual para clonar
                $date_range_hotels = DB::table('date_range_hotels')
                    ->where('rate_plan_id', $rate_plan->id)
                    ->where('date_from', 'LIKE', "%" . $year_to . "%")
                    ->get();

                if ($date_range_hotels->count() === 0) {
                    $this->info("No hay tarifas para el año {$year_to}, clonando desde {$year}");

                    $date_range_hotels = DB::table('date_range_hotels')
                        ->where('rate_plan_id', $rate_plan->id)
                        ->where('date_from', 'LIKE', "%" . $year . "%")
                        ->where('flag_migrate', 0)
                        ->orderBy('group', 'asc')
                        ->get();

                    if (count($date_range_hotels) > 0) {
                        $max_value_group = DB::table('date_range_hotels')
                            ->where('rate_plan_id', $rate_plan->id)
                            ->max('group') + 1;

                        $recent_group = $date_range_hotels[0]->group;
                        $periodo = $year_to;

                        foreach ($date_range_hotels as $date_range_hotel) {
                            if ($date_range_hotel->group != $recent_group) {
                                $recent_group = $date_range_hotel->group;
                                $max_value_group++;
                            }

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
                                'updated' => 9,
                                'flag_migrate' => 1,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                        }

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
                        $this->info("Tarifa {$rate_plan->id} clonada para el año {$year_to}");
                    }
                } 
            }
        }

        $this->info("Proceso completado para el hotel ID: {$hotel_id}");
    }
}