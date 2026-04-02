<?php

namespace App\Console\Commands;

use App\ServiceClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DisableServiceClientVista extends Command
{
    private $client_id = 16868;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:disable_client_vista {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deshabilita servicios para el cliente vista';

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
        $year = $this->argument('year');
        ServiceClient::where('client_id', $this->client_id)->where('period', $year)->forceDelete();

        $services_data = [];

        $services_to_block = DB::table('services')
            ->select('services.id', 'services.name')
            ->join('service_sub_categories', 'service_sub_categories.id', '=', 'services.service_sub_category_id')
            ->join('service_categories', 'service_categories.id', '=', 'service_sub_categories.service_category_id')
            ->whereNotIn('service_sub_categories.service_category_id', [1, 2, 9, 10, 14])
            ->where('services.status', '=', 1)
            ->get();

        foreach ($services_to_block as $service) {
            array_push($services_data, [
                'period' => $year,
                'client_id' => $this->client_id,
                'service_id' => $service->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        ServiceClient::insert($services_data);
    }
}
