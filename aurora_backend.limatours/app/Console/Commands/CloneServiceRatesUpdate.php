<?php

namespace App\Console\Commands;

use App\CloneLog;
use App\ConfigMarkup;
use App\Service;
use App\ServiceRatePlan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneServiceRatesUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:clone_rates_update';

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
        $year = Carbon::now()->year;
        $year_to = Carbon::now()->year + 1;

        $config_markups = ConfigMarkup::where('type', '=', 1)
            ->where('percent', '=', 0)
            ->where('status', '=', 1)->orderBy('id', 'desc')
            ->pluck('category_id')->toArray(); // [1, 2, 0]

        if(count($config_markups) > 0)
        {
            if(in_array('0', $config_markups))
            {
                CloneLog::where('type', '=', 1)
                    ->whereIn('category_id', $config_markups)
                    ->where('status', '=', 1)
                    ->update([
                        'status' => 0
                    ]);
            }

            Service::where('status', '=', 1)
                ->whereNotIn('service_sub_category_id', [22, 23]) // No se considera lodge y cruceros
                ->with(['service_rate' => function ($query) {
                    $query->orWhere('flag_process_markup', '=', 1);
                    $query->orWhereNull('flag_process_markup');
                }, 'serviceSubCategory'])
                ->whereHas('serviceSubCategory', function ($query) use ($config_markups) {
    
                    if(count($config_markups) > 0 && !in_array('0', $config_markups))
                    {
                        $query->whereIn('service_category_id', $config_markups);
                    }
                })
                ->whereHas('service_rate', function ($query) {
                    $query->orWhere('flag_process_markup', '=', 1);
                    $query->orWhereNull('flag_process_markup');
                })
                ->whereDoesntHave('client_services')
                ->orderBy('services.id', 'ASC')
                ->chunk(10, function ($services) use ($year, $year_to, $config_markups) {

                    if(count($services) == 0)
                    {
                        ConfigMarkup::where('type', '=', 1)
                            ->whereIn('category_id', $config_markups)
                            ->where('status', '=', 1)->orderBy('id', 'desc')
                            ->update([
                                'percent' => 100,
                                'status' => 2
                            ]);
                    }
    
                    foreach ($services as $service) {

                        $max = Service::where('status', '=', 1)
                        ->whereNotIn('service_sub_category_id', [22, 23]) // No se considera lodge y cruceros
                        ->whereHas('serviceSubCategory',
                            function ($query) use ($config_markups) {
                                if(count($config_markups) > 0 && !in_array('0', $config_markups))
                                {
                                    $query->whereIn('service_category_id', $config_markups);
                                }
                            })->whereHas('service_rate', function ($query) {
                                $query->orWhere('flag_process_markup', '=', 1);
                                $query->orWhereNull('flag_process_markup');
                            })->max('id');
    
                        // Traer el markup pendiente..
                        $config_markup = ConfigMarkup::where('type', '=', 1)
                            ->whereIn('category_id', $config_markups)
                            ->where('status', '=', 1)->orderBy('id', 'desc')
                            ->first();
    
                        $markup = 1; $markup_prev = 0; $process = false;
    
                        if ($config_markup)
                        {
                            $markup = ($config_markup->markup / 100) + 1;
                            $markup_prev = ($config_markup->prev / 100);

                            $process = true;
                        }
    
                        foreach ($service->service_rate as $rate_plan) {
                            if (!($rate_plan->promotions)) {
                                // SI NO ES PROMOCIONAL
    
                                if($rate_plan->flag_process_markup === 0) // tiene que ser 0 para ignorar
                                {
                                    $process = false;
                                }
    
                                // En caso se ignore los margenes de protección para la tarifa asociada
                                if($process)
                                {
                                    $has_migrate = DB::table('service_rate_plans')
                                        ->where('service_rate_id', $rate_plan->id)
                                        ->where('flag_migrate', '<>', 1)
                                        ->where('date_from', 'LIKE', "%".$year_to."%")
                                        ->whereNull('deleted_at')
                                        ->orderBy('created_at', 'desc')->count();
    
                                    if ($has_migrate == 0) { // no hay tarifas sinceradas..

                                        $date_range_services = DB::table('service_rate_plans')
                                            ->where('service_rate_id', $rate_plan->id)
                                            ->where(function ($query) {
                                                $query->orWhere('flag_migrate', '=', 1);
                                                $query->orWhereNull('flag_migrate');
                                            })
                                            ->whereNull('deleted_at')
                                            ->where('date_from', 'LIKE', "%".$year_to."%")
                                            ->orderBy('created_at', 'desc');

                                        $date_range_services_ids = $date_range_services->pluck('id');
        
                                        if (count($date_range_services_ids) === 0) {
                                            // SI EL AÑO DESTINO NO TIENE TARIFAS
        
                                            // Rates año origen usadas para copiar
                                            $date_range_services = DB::table('service_rate_plans')
                                                ->where('service_rate_id', $rate_plan->id)
                                                ->whereNull('deleted_at')
                                                ->where('date_from', 'LIKE', "%".$year."%")
                                                ->get();
        
                                            if (count($date_range_services) > 0) {
                                                // var_export( json_encode( $date_range_services ) ); // Revisar los cortes de 1 a 9 dias

                                                CloneLog::where('type', '=', 'service')
                                                    ->where('category_id', '=', $service->serviceSubCategory->service_category_id)
                                                    ->where('item_id', '=', $service->id)
                                                    ->whereIn('item_rate_id', $date_range_services_ids)
                                                    ->where('status', '=', 1)
                                                    ->update([
                                                        'status' => 0
                                                    ]);
        
                                                foreach ($date_range_services as $date_range_service) {
        
                                                    //$date_range_service_date_from = Carbon::parse($date_range_service->date_from);
                                                    //$date_range_service_date_to = Carbon::parse($date_range_service->date_to);
        
                                                    //$diff_dates = $date_range_service_date_from->diffInDays($date_range_service_date_to);
        
                                                    //if ($diff_dates > 4) {
        
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
        
                                                        if ($markup_prev > 0) {
                                                            $date_range_service->price_adult = $date_range_service->price_adult - ($date_range_service->price_adult * $markup_prev);
                                                            $date_range_service->price_child = $date_range_service->price_child - ($date_range_service->price_child * $markup_prev);
                                                            $date_range_service->price_infant = $date_range_service->price_infant - ($date_range_service->price_infant * $markup_prev);
                                                            $date_range_service->price_guide = $date_range_service->price_guide - ($date_range_service->price_guide * $markup_prev);
                                                        }

                                                        $new_date_from = str_replace($year, $year_to, $date_range_service->date_from);
                                                        $new_date_to = str_replace($year, $year_to, $date_range_service->date_to);

                                                        // Configuración para años bisiestos ---------------------------
                                                        if($date_range_service->date_from == $year . '-02-29')
                                                        {
                                                            $fecha = Carbon::parse($year_to . '-02-01');
                                                            $new_date_from = $fecha->endOfMonth();
                                                        }

                                                        if($date_range_service->date_to == $year . '-02-29')
                                                        {
                                                            $fecha = Carbon::parse($year_to . '-02-01');
                                                            $new_date_to = $fecha->endOfMonth();
                                                        }
                                                        // --------------------------------------------------------------

                                                        $service_rate_plan = new ServiceRatePlan();
                                                        $service_rate_plan->service_rate_id = $date_range_service->service_rate_id;
                                                        $service_rate_plan->date_from = $new_date_from;
                                                        $service_rate_plan->date_to = $new_date_to;
                                                        $service_rate_plan->price_adult = $date_range_service->price_adult * $markup;
                                                        $service_rate_plan->price_child = $date_range_service->price_child * $markup;
                                                        $service_rate_plan->price_infant = $date_range_service->price_infant * $markup;
                                                        $service_rate_plan->price_guide = $date_range_service->price_guide * $markup;
                                                        $service_rate_plan->pax_from = $date_range_service->pax_from;
                                                        $service_rate_plan->pax_to = $date_range_service->pax_to;
                                                        $service_rate_plan->monday = $date_range_service->monday;
                                                        $service_rate_plan->tuesday = $date_range_service->tuesday;
                                                        $service_rate_plan->wednesday = $date_range_service->wednesday;
                                                        $service_rate_plan->thursday = $date_range_service->thursday;
                                                        $service_rate_plan->friday = $date_range_service->friday;
                                                        $service_rate_plan->saturday = $date_range_service->saturday;
                                                        $service_rate_plan->sunday = $date_range_service->sunday;
                                                        $service_rate_plan->user_id = $date_range_service->user_id;
                                                        $service_rate_plan->service_cancellation_policy_id = $date_range_service->service_cancellation_policy_id;
                                                        $service_rate_plan->status = 1;
                                                        $service_rate_plan->flag_migrate = 1;
                                                        $service_rate_plan->save();

                                                        $log->item_rate_plan_id = $service_rate_plan->id;
                                                        $log->save();
                                                    //}
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
    
                        if ($config_markup)
                        {
                            $percent = number_format($service->id / $max * 100, 2);
    
                            $config_markup->percent = $percent;
                            $config_markup->save();
    
                            if ($service->id == $max)
                            {
                                $config_markup->percent = 100;
                                $config_markup->status = 2;
                                $config_markup->save();
                            }
                        }
                    }
                });
        }
    }
}
