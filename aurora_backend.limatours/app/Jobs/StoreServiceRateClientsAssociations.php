<?php

namespace App\Jobs;

use App\Client;
use App\ServiceClientRatePlan;
use App\ServiceRateAssociation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class StoreServiceRateClientsAssociations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user_id;
    public $rate_plan_id;
    public $year;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($rate_plan_id, $year, $user_id)
    {
        $this->rate_plan_id = $rate_plan_id;
        $this->year = $year;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {

            $rate_plan_associations_market = collect();


            //Todo Eliminamos la realaciones de las tarifas por el periodo (año)
            ServiceClientRatePlan::where('service_rate_id', $this->rate_plan_id)->where('period',
                $this->year)->forceDelete();

            //Todo Verificamos si existe algun filtro de bloqueo para la tarifa del servicios
            $countAssociations = ServiceRateAssociation::where('service_rate_id', $this->rate_plan_id)->limit(1)->get();

            //Todo Si hay registros se realiza el bloqueo segun los filtros
            if ($countAssociations->count() > 0) {
                //Todo Buscamos por la entidad si hay un bloqueo por mercado
                $clients_rate_plan_associations_m = ServiceRateAssociation::where('entity', 'Market')
                    ->where('service_rate_id', $this->rate_plan_id);

                if ($clients_rate_plan_associations_m->count() > 0) {
                    $rate_plan_associations_market = Client::whereIn('market_id',
                        $clients_rate_plan_associations_m->pluck('object_id'))->get(['id', 'market_id', 'country_id']);
                }

                //Todo Buscamos por la entidad si hay un bloqueo por Pais
                $clients_rate_plan_associations_country = ServiceRateAssociation::where('entity', 'Country')
                    ->where('service_rate_id', $this->rate_plan_id)->get(['entity', 'object_id', 'except']);

                if ($clients_rate_plan_associations_country->count() > 0) {
                    if (!$clients_rate_plan_associations_country->first()->except) {
                        $rate_plan_associations_market = $rate_plan_associations_market->whereIn('country_id',
                            $clients_rate_plan_associations_country->pluck('object_id'));
                    } else {
                        $rate_plan_associations_market = $rate_plan_associations_market->whereNotIn('country_id',
                            $clients_rate_plan_associations_country->pluck('object_id'));
                    }
                }

                //Todo Buscamos por la entidad si hay un bloqueo por cliente
                $clients_rate_plan_associations_clients = ServiceRateAssociation::where('entity', 'Client')
                    ->where('service_rate_id', $this->rate_plan_id)->get(['entity', 'object_id', 'except']);;
                if ($clients_rate_plan_associations_clients->count() > 0) {

                    if (!$clients_rate_plan_associations_clients->first()->except) {
                        $rate_plan_associations_market = $rate_plan_associations_market->whereIn('id',
                            $clients_rate_plan_associations_clients->pluck('object_id'));
                    } else {
                        $rate_plan_associations_market = $rate_plan_associations_market->whereNotIn('id',
                            $clients_rate_plan_associations_clients->pluck('object_id'));
                    }
                }

                $rate_plan_associations_market = $rate_plan_associations_market->values();

                //Todo Recorre los clientes y los inserta
                Client::whereNotIn('id', $rate_plan_associations_market->pluck('id'))->chunk(250, function ($clients) {
                    foreach ($clients as $client) {
                        ServiceClientRatePlan::insert([
                            'period' => $this->year,
                            'client_id' => $client->id,
                            'service_rate_id' => $this->rate_plan_id,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                });

            }
        });

    }
}
