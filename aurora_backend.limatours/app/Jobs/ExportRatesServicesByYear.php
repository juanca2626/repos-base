<?php

namespace App\Jobs;

use App\City;
use App\Client;
use App\Language;
use App\Markup;
use App\Service;
use App\ServiceCategory;
use App\ServiceClient;
use App\State;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExportRatesServicesByYear implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 280;

    public $service_year;
    public $lang;
    public $client_id;
    public $user;
    public $document_name_store;
    public $log_rate_id;

    /**
     * Create a new job instance.
     *
     * @param int $service_year
     * @param string $lang
     * @param int $client_id
     * @param int $user
     * @param string $document_name_store
     * @param int $log_rate_id
     */
    public function __construct(
        $service_year = 0,
        $lang = 'en',
        $client_id = 0,
        $user = 0,
        $document_name_store = 'excel',
        $log_rate_id = 0
    )
    {
        $this->service_year = $service_year;
        $this->lang = $lang;
        $this->client_id = $client_id;
        $this->user = $user;
        $this->document_name_store = $document_name_store;
        $this->log_rate_id = $log_rate_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $services = new Service();
        $data = $services->ratesServicesByYearUpdate($this->service_year,$this->lang,$this->client_id);

        Excel::store(new  \App\Exports\ServiceCityExport($this->service_year, $this->lang, $this->client_id, $data),
            $this->document_name_store . '.xlsx');
    }
}
