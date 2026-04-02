<?php

namespace App\Console\Commands;

use App\Client;
use Illuminate\Console\Command;

class CallDisabledRatesPlansServicesByClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:call_disabled_rates_services_client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hoteles - Deshabilita las tarifas a un listado de clientes de acuerdo a la asociacion';

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
        $clients = Client::where('status', 1)->where('market_id', 21)->get();

        $progressbar = $this->output->createProgressBar($clients->count());

        $progressbar->start();

        foreach ($clients as $client) {
            $this->call('aurora:disabled_rates_services_client', ['client_id' => $client->id]);
            $progressbar->advance();
        }

        $progressbar->finish();

    }
}
