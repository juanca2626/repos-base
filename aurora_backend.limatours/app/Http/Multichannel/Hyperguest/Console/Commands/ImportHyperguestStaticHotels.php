<?php

namespace App\Http\Multichannel\Hyperguest\Console\Commands;

use App\Http\Multichannel\Hyperguest\Jobs\ImportHyperguestStaticHotelsJob;
use Exception;
use Illuminate\Console\Command;

class ImportHyperguestStaticHotels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyperguest:import-static-hotels
                            {--country= : Código de país (ej: PT, ES). Si no se especifica, importa todos los hoteles}
                            {--hotel-ids= : IDs de hoteles específicos separados por coma (ej: 19837,19912,20000)}
                            {--queue=hyperguest_static_import : Nombre de la cola donde se ejecutará el job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa datos estáticos de hoteles de Hyperguest a la tabla hyperguest_hotels';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('[ImportHyperguestStaticHotels] Iniciando importación de hoteles estáticos...');

        try {
            $country = $this->option('country');
            $queueName = $this->option('queue');
            $hotelIdsOption = $this->option('hotel-ids');

            // Procesar hotel_ids si se proporcionan
            $hotelIds = null;
            if ($hotelIdsOption) {
                $hotelIds = array_map(function($id) {
                    return trim($id);
                }, explode(',', $hotelIdsOption));
                $hotelIds = array_filter($hotelIds, function($id) {
                    return !empty($id) && is_numeric($id);
                });
                $hotelIds = !empty($hotelIds) ? array_values($hotelIds) : null;
            }

            if ($country) {
                $this->info("[ImportHyperguestStaticHotels] País especificado: {$country}");
            } else {
                $this->info("[ImportHyperguestStaticHotels] Importando todos los hoteles (sin filtro de país)");
            }

            if ($hotelIds) {
                $this->info("[ImportHyperguestStaticHotels] IDs de hoteles especificados: " . implode(', ', $hotelIds));
            }

            $this->info("[ImportHyperguestStaticHotels] Cola: {$queueName}");

            // Despachar el job a la cola especificada
            ImportHyperguestStaticHotelsJob::dispatch($country, $queueName, $hotelIds)
                ->onQueue($queueName);

            $this->info('[ImportHyperguestStaticHotels] Job despachado correctamente a la cola: ' . $queueName);
            $this->info('[ImportHyperguestStaticHotels] Para procesar el job, ejecuta: php artisan queue:work --queue=' . $queueName);

            return 0;

        } catch (Exception $e) {
            $this->error('[ImportHyperguestStaticHotels] Error al despachar el job: ' . $e->getMessage());
            $this->error('[ImportHyperguestStaticHotels] Trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}

