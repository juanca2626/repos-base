<?php

namespace App\Http\Multichannel\Hyperguest\Console\Commands;

use App\Http\Multichannel\Hyperguest\Jobs\HotelSyncJob;
use Exception;
use Illuminate\Console\Command;

class HotelSyncCommand extends Command
{
    protected $signature = 'sync:hotels {--limit=0 : Cantidad de hoteles a traer} {--countries= : Lista de países separados por coma, ejemplo: CO,PE,EC} {--from=0 : Índice inicial} {--hotel_ids= : IDS de hoteles específico a sincronizar}';
    protected $description = 'Sincroniza hoteles desde Hyperguest con sistema legacy';

    public function handle()
    {
        $this->info("[HotelSyncCommand] Intentando job de sincronización de hoteles...");
        try {
            $limit = (int) $this->option('limit');
            $countriesOption = $this->option('countries');
            $countries = $countriesOption ? explode(',', $countriesOption) : [];
            $from = (int) $this->option('from');
            $hotel_ids_option = $this->option('hotel_ids');
            $hotel_ids = $hotel_ids_option ? explode(',', $hotel_ids_option) : [];

            $this->info("[HotelSyncCommand] parametros limit: $limit, countriesOption:  $countriesOption, from: $from, hotels_ids: $hotel_ids_option");

            HotelSyncJob::dispatch($limit, $countries, $from, $hotel_ids);
            $this->info("[HotelSyncCommand] Job de sincronización de hoteles despachado correctamente");
        } catch (Exception $e) {
            $this->error("[HotelSyncCommand] Error al despachar el job de sincronización de hoteles: " . $e->getMessage());
        }
    }
}
