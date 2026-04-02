<?php

namespace App\Console\Commands;

use App\Service;
use App\ServiceRatePlan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneServiceRateMarginOfProtection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:clone-rate-margin-of-protection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clona las tarifas de servicios aplicando un margen de protección manual.';

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

        $this->info('Iniciando proceso de clonado de tarifas de servicios');

        $total_servicios_procesados = 0;
        $total_servicios_omitidos = 0;
        $total_tarifas_clonadas = 0;

        // Definir el markup manualmente
        //$markup = 1.07; // Por ejemplo, un markup del 10% (1 + 0.10)

        // Procesar los servicios en chunks de 10
        Service::where('status', '=', 1)
            ->whereNotIn('service_sub_category_id', [23]) // No se considera cruceros (Lodges id 22 sí se incluyen ahora)
            ->with(['service_rate', 'serviceSubCategory']) // Cargar relaciones
            ->orderBy('services.id', 'ASC')
            ->chunk(10, function ($services) use ($year, $year_to, &$total_servicios_procesados, &$total_servicios_omitidos, &$total_tarifas_clonadas) {
                foreach ($services as $service) {
                    $servicio_actualizado = false;

                    if (stripos((string) $service->aurora_code, 'TRN') === 0) {
                        $markup = 1.07;
                        $this->info('Clonando tarifas 7% de TREN ' . ($service->aurora_code ?? '') . ' para ' . $year_to . " - Markup: " . $markup);
                    } elseif ($service->service_sub_category_id == 22) {
                        $markup = 1.10;
                        $this->info('Clonando tarifas 10% de LODGE ' . ($service->aurora_code ?? '') . ' para ' . $year_to . " - Markup: " . $markup);
                    } else {
                        continue;
                    }

                    foreach ($service->service_rate as $rate_plan) {
                        $markup_actual = $markup;

                        // Si el nombre contiene Dinámica o Dinamica, usar markup 1.0 (0% incremento)
                        if (stripos($rate_plan->name, 'dinámica') !== false || stripos($rate_plan->name, 'dinamica') !== false) {
                            $markup_actual = 1.0;
                        }

                        if (!($rate_plan->promotions)) { // Si no es promocional
                            // Verificar si hay tarifas migradas
                            $has_migrate = DB::table('service_rate_plans')
                                ->where('service_rate_id', $rate_plan->id)
                                ->where('flag_migrate', '<>', 1)
                                ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                ->whereNull('deleted_at')
                                ->orderBy('created_at', 'desc')
                                ->count();

                            if ($has_migrate == 0) { // No hay tarifas sinceradas
                                // Obtener tarifas del año destino
                                $date_range_services = DB::table('service_rate_plans')
                                    ->where('service_rate_id', $rate_plan->id)
                                    ->where(function ($query) {
                                        $query->orWhere('flag_migrate', '=', 1);
                                        $query->orWhereNull('flag_migrate');
                                    })
                                    ->whereNull('deleted_at')
                                    ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                    ->orderBy('created_at', 'desc')
                                    ->get();

                                if ($date_range_services->count() === 0) { // Si no hay tarifas para el año destino
                                    // Obtener tarifas del año origen
                                    $date_range_services = DB::table('service_rate_plans')
                                        ->where('service_rate_id', $rate_plan->id)
                                        ->where('flag_migrate', '=', 0)
                                        ->whereNull('deleted_at')
                                        ->where('date_from', 'LIKE', "%" . $year . "%")
                                        ->get();

                                    if (count($date_range_services) > 0) {
                                        foreach ($date_range_services as $date_range_service) {

                                            // Configuración para años bisiestos
                                            $new_date_from = str_replace($year, $year_to, $date_range_service->date_from);
                                            $new_date_to = str_replace($year, $year_to, $date_range_service->date_to);

                                            if ($date_range_service->date_from == $year . '-02-29') {
                                                $fecha = Carbon::parse($year_to . '-02-01');
                                                $new_date_from = $fecha->endOfMonth();
                                            }

                                            if ($date_range_service->date_to == $year . '-02-29') {
                                                $fecha = Carbon::parse($year_to . '-02-01');
                                                $new_date_to = $fecha->endOfMonth();
                                            }

                                            // Crear la nueva tarifa con el markup aplicado
                                            $service_rate_plan = new ServiceRatePlan();
                                            $service_rate_plan->service_rate_id = $date_range_service->service_rate_id;
                                            $service_rate_plan->date_from = $new_date_from;
                                            $service_rate_plan->date_to = $new_date_to;
                                            $service_rate_plan->price_adult = $date_range_service->price_adult * $markup_actual;
                                            $service_rate_plan->price_child = $date_range_service->price_child * $markup_actual;
                                            $service_rate_plan->price_infant = $date_range_service->price_infant * $markup_actual;
                                            $service_rate_plan->price_guide = $date_range_service->price_guide * $markup_actual;
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
                                            $service_rate_plan->flag_migrate = 1; // Indicar que es una tarifa duplicada
                                            $service_rate_plan->save();

                                            $total_tarifas_clonadas++;
                                            $servicio_actualizado = true;
                                        }
                                        $this->info('Servicio actualizado: ' . $service->id.' - ' . $service->aurora_code ?? ''.' Markup aplicado: ' . $markup_actual);
                                    }
                                }
                            }
                        }
                    }

                    if ($servicio_actualizado) {
                        $total_servicios_procesados++;
                    } else {
                        $total_servicios_omitidos++;
                    }
                }
            });

        $this->info('--------------- RESUMEN DE EJECUCIÓN ---------------');
        $this->info("Total servicios con clonación procesada: {$total_servicios_procesados}");
        $this->info("Total servicios omitidos: {$total_servicios_omitidos}");
        $this->info("Total de tarifas de servicios insertadas: {$total_tarifas_clonadas}");
        $this->info('----------------------------------------------------');
        $this->info('Comando CloneServiceRateMarginOfProtection ejecutado correctamente.');
    }
}
