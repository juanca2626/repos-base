<?php

namespace App\Console\Commands;

use App\Service;
use App\ServiceClient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DisableServicesClientMasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:disable_client_masi {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deshabilitar servicios para el cliente masi';

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
        $services_to_block = Service::whereNotIn('aurora_code',['LIN40A','LIN432','LIN435','LIN618','LIN58A','LIN424','LIN613','LIN615','LIN40P','LIN405','LIN41M','LIN41N','LIN542','LINXMP','AQV416'
            ,'AQVP21','AQV502','AQV401','AQVP01','AQV415','PUV407','PUV508','PUVP05','PUV417','PUV416','PUV402','PUV506','PUVP12','CUZ557','CUZ446','CUZ434','CUZ441','CUZ573',
        'CUZ574','CUZ575','CUZ528','CUZ418','CUZ5P6','CUZ5P7','CUZ438','CUZ414','CUZXTP','UR1X13','BCOMP3','BCOMP2','BCOMP1'])->pluck('id')->toArray();

        $year = $this->argument('year');

        ServiceClient::where('client_id',16980)->where('period',$year)->forceDelete();

        $services_data = [];

        foreach ($services_to_block as $service_id)
        {
            array_push($services_data,[
                'period'=>$year,
                'client_id'=>16980,
                'service_id'=>$service_id,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ]);
        }

        ServiceClient::insert($services_data);
    }
}
