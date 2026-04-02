<?php

namespace App\Console\Commands;

use App\CloneLog;
use App\ConfigMarkup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Service;
use Illuminate\Support\Facades\DB;

class CloneServiceRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:clone_rates';

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
            $max = Service::max('id');

            ConfigMarkup::where('type', '=', 1)
                ->where('percent', '=', 100)
                ->where('status', '>', 0)->update([
                    'percent' => 0,
                ]);

            Service::with(['service_rate', 'serviceSubCategory'])
                ->orderBy('services.id', 'ASC')
//                ->where('id', 4)
                ->chunk( 5, function($services) use ($year, $year_to, $max) {

                    foreach ( $services as $k_service => $service ) {

                        // Traer el markup pendiente..
                        $config_markup = ConfigMarkup::where('type', '=', 1)
                            ->where('category_id', '=', $service->serviceSubCategory->service_category_id)
                            ->where('percent', '=', 0)
                            ->where('status', '>', 0)->orderBy('id', 'desc')->first();

                        $markup = 1; $update_markup = FALSE; $process = TRUE;

                        if($config_markup != null)
                        {
                            $markup = ($config_markup->markup / 100) + 1;

                            if($config_markup->created_at != $config_markup->updated_at)
                            {
                                $update_markup = TRUE;
                            }
                        }

                        foreach ($service->service_rate as $rate_plan) {
                            if( !($rate_plan->promotions) ){ // SI NO ES PROMOCIONAL

                                // En caso se ignore los margenes de protección para la tarifa asociada
                                if($rate_plan->flag_process_markup === 0) // tiene que ser 0 para ignorar
                                {
                                    $process = FALSE;
                                }

                                if($process)
                                {
                                    if($update_markup)
                                    {
                                        $has_migrate = DB::table('service_rate_plans')
                                            ->where('service_rate_id', $rate_plan->id)
                                            ->where('flag_migrate', '<>', 1)
                                            ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                            ->whereNull('deleted_at')
                                            ->orderBy('created_at', 'desc')->count();

                                        if($has_migrate == 0)
                                        {
                                            // Eliminar las tarifas en caso todas las que existan sean duplicadas..
                                            $date_range_services = DB::table('service_rate_plans')
                                                ->where('service_rate_id', $rate_plan->id)
                                                ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                                ->update([
                                                    'deleted_at' => Carbon::now(),
                                                ]);
                                        }
                                    }

                                    $date_range_services = DB::table('service_rate_plans')
                                        ->where('service_rate_id', $rate_plan->id)
                                        ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                        ->where(function ($query) {
                                            $query->orWhere('flag_migrate', '=', 1);
                                            $query->orWhereNull('flag_migrate');
                                        })
                                        ->whereNull('deleted_at')
                                        ->orderBy('created_at', 'desc')->get();

                                    if ($date_range_services->count() === 0) { // SI EL AÑO DESTINO NO TIENE TARIFAS

                                        // Rates año origen usadas para copiar
                                        $date_range_services = DB::table('service_rate_plans')
                                            ->where('service_rate_id', $rate_plan->id)
                                            ->where('date_from', 'LIKE', "%" . $year . "%")
                                            ->whereNull('deleted_at')
                                            ->get();

                                        if( count($date_range_services) > 0) {
    //                                        var_export( json_encode( $date_range_services ) ); // Revisar los cortes de 1 a 9 dias

                                            foreach ($date_range_services as $date_range_service) {

                                                $date_range_service_date_from = Carbon::parse( $date_range_service->date_from );
                                                $date_range_service_date_to = Carbon::parse( $date_range_service->date_to );

                                                $diff_dates = $date_range_service_date_from->diffInDays($date_range_service_date_to);

                                                if( $diff_dates > 4 )
                                                {
                                                    CloneLog::where('type', '=', 'service')
                                                        ->where('category_id', '=', $service->serviceSubCategory->service_category_id)
                                                        ->where('item_id', '=', $service->id)
                                                        ->where('item_rate_id', '=', $date_range_service->service_rate_id)
                                                        ->where('status', '=', 1)
                                                        ->update([
                                                            'status' => 0
                                                        ]);

                                                    // Generando log..
                                                    $log = new CloneLog;
                                                    $log->type = 'service'; // Hotel
                                                    $log->category_id = $service->serviceSubCategory->service_category_id;
                                                    $log->item_id = $service->id;
                                                    $log->item_rate_id = $date_range_service->service_rate_id;
                                                    $log->date = Carbon::now();
                                                    $log->markup = $markup;
                                                    $log->status = 1;
                                                    $log->save();

                                                    DB::table('service_rate_plans')->insert([
                                                        'service_rate_id' => $date_range_service->service_rate_id,
                                                        'date_from' => str_replace($year, $year_to, $date_range_service->date_from),
                                                        'date_to' => str_replace($year, $year_to, $date_range_service->date_to),
                                                        'price_adult' => $date_range_service->price_adult * $markup,
                                                        'price_child' => $date_range_service->price_child * $markup,
                                                        'price_infant' => $date_range_service->price_infant * $markup,
                                                        'price_guide' => $date_range_service->price_guide * $markup,
                                                        'pax_from' => $date_range_service->pax_from,
                                                        'pax_to' => $date_range_service->pax_to,
                                                        'monday' => $date_range_service->monday,
                                                        'tuesday' => $date_range_service->tuesday,
                                                        'wednesday' => $date_range_service->wednesday,
                                                        'thursday' => $date_range_service->thursday,
                                                        'friday' => $date_range_service->friday,
                                                        'saturday' => $date_range_service->saturday,
                                                        'sunday' => $date_range_service->sunday,
                                                        'user_id' => $date_range_service->user_id,
                                                        'service_cancellation_policy_id' => $date_range_service->service_cancellation_policy_id,
                                                        'status' => 1,
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
                            $percent = number_format($service->id / $max * 100, 2);

                            $config_markup->percent = $percent;
                            $config_markup->save();

                            if($service->id == $max)
                            {
                                $config_markup->percent = 100;
                                $config_markup->status = 2;
                                $config_markup->save();

                                /*
                                ConfigMarkup::where('type', '=', 1)
                                    ->where('status', '>', 0)->update([
                                        'status' => 2,
                                        'percent' => 100
                                    ]);
                                */
                            }
                        }
                    }
                });
        // });
    }
}
