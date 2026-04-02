<?php

namespace App\Jobs;

use App\ServiceClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DuplicateServiceClients implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;

    protected $serviceClients;
    protected $service_id;
    protected $year_to;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($service_id, $serviceClients, $year_to)
    {
        $this->serviceClients = $serviceClients;
        $this->service_id = $service_id;
        $this->year_to = $year_to;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //insertar client_hotels de year_to nuevos
        foreach ($this->serviceClients as $service_client) {
//            $exist = ServiceClient::where('client_id', $service_client->client_id)
//                ->where('period', $this->year_to)
//                ->where('service_id', $this->service_id);
//            if ($exist->count() == 0) {
                $new_service_client = new ServiceClient();
                $new_service_client->period = $this->year_to;
                $new_service_client->client_id = $service_client->client_id;
                $new_service_client->service_id = $this->service_id;
                $new_service_client->save();
//            }

        }
    }
}
