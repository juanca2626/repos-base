<?php

namespace App\Console\Commands;

use App\Client;
use App\Markup;
use Illuminate\Console\Command;

class MarkupGeneralYear extends Command
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'markup:general-year {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el markup general para el año especificado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?? date('Y');

        // Lógica del comando
        $this->info("Ejecutando markup general para el año $year...");

        // Obtener los clientes con sus respectivos markups
        $clients = Client::select([
            'clients.id',
            'clients.code',
            'clients.name',
            'markets.name as mercado',
            'clients.general_markup',
            'markups.hotel as markup_hotel',
            'markups.service as markup_service',
            'clients.status',
            'markups.id as markup_id'
        ])
        ->join('markups', 'clients.id', '=', 'markups.client_id')
        ->join('markets', 'markets.id', '=', 'clients.market_id')
        ->where('clients.status', 1)
        ->whereNull('clients.deleted_at')
        ->where('markups.period', $year)
        ->where(function ($query) {
            $query->where('clients.general_markup', 17)
                  ->orWhere('markups.hotel', 17)
                  ->orWhere('markups.service', 17);
        })
        ->get();

        foreach ($clients as $client) {
            $updates = [];

            // Actualizar markup general del cliente si es 17
            if ($client->general_markup == 17) {
                Client::where('id', $client->id)->update(['general_markup' => 18]);
                $updates[] = 'general_markup';
            }

            // Obtener markup del cliente solo una vez
            $markup = Markup::where('id', $client->markup_id)->first();

            if ($markup) {
                $markupUpdated = false;

                // Actualizar markup hotel si es 17
                if ($markup->hotel == 17) {
                    $markup->hotel = 18;
                    $markupUpdated = true;
                    $updates[] = 'markup_hotel';
                }

                // Actualizar markup service si es 17
                if ($markup->service == 17) {
                    $markup->service = 18;
                    $markupUpdated = true;
                    $updates[] = 'markup_service';
                }

                if ($markupUpdated) {
                    $markup->save();
                }
            }

            // Mensaje de actualización
            if (!empty($updates)) {
                $this->info("Cliente {$client->id} actualizado: " . implode(', ', $updates));
            }
        }

        $this->info("Markup general para el año $year completado.");
    }
}
