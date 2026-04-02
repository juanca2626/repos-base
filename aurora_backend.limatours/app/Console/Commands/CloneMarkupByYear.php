<?php

namespace App\Console\Commands;

use App\Markup;
use App\MarkupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneMarkupByYear extends Command
{
    /**
     * El nombre y firma del comando de la consola.
     *
     * @var string
     */
    protected $signature = 'markup:clone {year}';

    /**
     * La descripción del comando de la consola.
     *
     * @var string
     */
    protected $description = 'Clona los datos de markup para el año especificado';

    /**
     * Crea una nueva instancia del comando.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ejecuta el comando de la consola.
     */
    public function handle()
    {
        $year = $this->argument('year');
        $previousYear = $year - 1;
        // Validar que el año sea un número válido
        if (!is_numeric($year) || strlen($year) !== 4) {
            $this->error('El año debe ser un número de 4 dígitos.');
            return 1;
        }

        // Lógica para clonar el markup
        $this->info("Clonando markup para el año {$year}...");

        $markups = Markup::where('period', $previousYear)
            ->where('status', 1)
            ->get();

        $clientRegions = DB::table('business_region_client')
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('client_id')
            ->map(function ($rows) {
                return $rows->pluck('business_region_id')->all();
            });

        if ($markups->isEmpty()) {
            $this->error("No hay registros en 'markups' para el año {$previousYear} con status 1.");
            return 1;
        }

        // Iterar sobre los registros y clonarlos si no existen ya en el nuevo año
        $clonedCount = 0;
        foreach ($markups as $markup) {
            $regionsForClient = $clientRegions->get($markup->client_id, []);

            if (empty($markup->business_region_id)) {
                // Solo clonamos markups que ya tienen region asignada
                continue;
            }

            if (!in_array($markup->business_region_id, $regionsForClient, true)) {
                continue;
            }

            $targetRegions = [$markup->business_region_id];

            foreach ($targetRegions as $regionId) {
                $exists = Markup::where('period', $year)
                    ->where('client_id', $markup->client_id)
                    ->where('business_region_id', $regionId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $markupNew = new Markup();
                $markupNew->period = $year;
                $markupNew->hotel = $markup->hotel;
                $markupNew->service = $markup->service;
                $markupNew->client_id = $markup->client_id;
                $markupNew->business_region_id = $regionId;
                $markupNew->clone = 1;
                $markupNew->status = $markup->status;
                $markupNew->save();

                $clonedCount++;
                $this->info("Se clono el markup para el client_id {$markup->client_id} y region {$regionId}.");
            }
        }

        //Clonar markup service
        /*$clonedCountService = 0;
        MarkupService::where('period', $previousYear)
            ->chunk(1000, function ($markupServices) use ($year) {
                foreach ($markupServices as $markup) {
                    // Verifica si ya existe antes de crear el nuevo registro
                    $exists = MarkupService::where('client_id', $markup->client_id)
                        ->where('service_id', $markup->service_id)
                        ->where('period', $year)
                        ->exists();

                    if (!$exists) {
                        MarkupService::create([
                            'client_id'   => $markup->client_id,
                            'service_id'  => $markup->service_id,
                            'period'      => $year,
                            'status'      => $markup->status,
                            'markup'      => $markup->markup,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                        $clonedCountService++;
                        $this->info("Se clono el markup para el client_id {$markup->client_id} and service_id {$markup->service_id}.");
          
                    }
                }
            });
        */

     
        if ($clonedCount > 0) {
            $this->info("Se clonaron {$clonedCount} registros de markup para el año {$year}.");
        } else {
            $this->warn("No se clonó ningún registro porque ya existen en el año {$year}.");
        }

        /*if ($clonedCountService > 0) {
            $this->info("Se clonaron {$clonedCountService} registros de markup_services para el año {$year}.");
        } else {
            $this->warn("No se clonó ningún registro porque ya existen en el año {$year}.");
        }*/

        return 0;
    }
}
