<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class SyncClientRegions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:client-regions';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna todas las regiones a todos los clientes y crea markups';

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

        $this->info('🚀 Iniciando proceso MASIVO...');

        $now = now();

        DB::beginTransaction();

        try {

            // =========================
            // 1. business_region_client
            // =========================
            DB::statement("
                INSERT INTO business_region_client (client_id, business_region_id, created_at)
                SELECT c.id, br.id, ?
                FROM clients c
                CROSS JOIN business_regions br
                WHERE c.status = 1
                  AND c.deleted_at IS NULL
                  AND br.deleted_at IS NULL
                  AND NOT EXISTS (
                      SELECT 1 
                      FROM business_region_client brc
                      WHERE brc.client_id = c.id
                        AND brc.business_region_id = br.id
                  )
            ", [$now]);

            $this->info('✔ business_region_client insertado');


            // =========================
            // 2. markups
            // =========================
            DB::statement("
                INSERT INTO markups 
                (period, hotel, service, status, client_id, clone, business_region_id, created_at)
                SELECT 
                    2026,
                    18,
                    18,
                    1,
                    c.id,
                    0,
                    br.id,
                    ?
                FROM clients c
                CROSS JOIN business_regions br
                WHERE c.status = 1
                  AND c.deleted_at IS NULL
                  AND br.deleted_at IS NULL
                  AND NOT EXISTS (
                      SELECT 1 
                      FROM markups m
                      WHERE m.client_id = c.id
                        AND m.business_region_id = br.id
                        AND m.period = 2026
                  )
            ", [$now]);

            $this->info('✔ markups insertado');


            DB::commit();

            $this->info('✅ Proceso finalizado CORRECTAMENTE');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
        }


    }
}
