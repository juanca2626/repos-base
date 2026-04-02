<?php

namespace App\Console\Commands;

use App\MarkupService;
use App\Service;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AddMarkupServicesClientMasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:add_markup_client_masi {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Agregar Markup personalizado de servicios a cliente MASI';

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
        $services = Service::select('id','aurora_code')->whereIn('aurora_code',['LIN40A','LIN432','LIN435','LIN618','LIN58A','LIN424','LIN613','LIN615','LIN40P','LIN405','LIN41M','LIN41N','LIN542','LINXMP','AQV416'
            ,'AQVP21','AQV502','AQV401','AQVP01','AQV415','PUV407','PUV508','PUVP05','PUV417','PUV416','PUV402','PUV506','PUVP12','CUZ557','CUZ446','CUZ434','CUZ441','CUZ573',
            'CUZ574','CUZ575','CUZ528','CUZ418','CUZ5P6','CUZ5P7','CUZ438','CUZ414','CUZXTP','UR1X13','BCOMP3','BCOMP2','BCOMP1'])->get()->toArray();
        $year = $this->argument('year');

        $service_markups = [
            ["aurora_code"=>"LIN40A", "markup"=>31 ],
            ["aurora_code"=>"LIN432", "markup"=>40 ],
            ["aurora_code"=>"LIN435", "markup"=>63 ],
            ["aurora_code"=>"LIN618", "markup"=>56 ],
            ["aurora_code"=>"LIN58A", "markup"=>20 ],
            ["aurora_code"=>"LIN424", "markup"=>24 ],
            ["aurora_code"=>"LIN613", "markup"=>24 ],
            ["aurora_code"=>"LIN615", "markup"=>48 ],
            ["aurora_code"=>"LIN40P", "markup"=>38 ],
            ["aurora_code"=>"LIN405", "markup"=>40 ],
            ["aurora_code"=>"LIN41M", "markup"=>20 ],
            ["aurora_code"=>"LIN41N", "markup"=>22 ],
            ["aurora_code"=>"LIN542", "markup"=>25 ],
            ["aurora_code"=>"LINXMP", "markup"=>39 ],
            ["aurora_code"=>"AQV416", "markup"=>30 ],
            ["aurora_code"=>"AQVP21", "markup"=>25 ],
            ["aurora_code"=>"AQV502", "markup"=>25 ],
            ["aurora_code"=>"AQV401", "markup"=>30 ],
            ["aurora_code"=>"AQVP01", "markup"=>25 ],
            ["aurora_code"=>"AQV415", "markup"=>30 ],
            ["aurora_code"=>"PUV407", "markup"=>30 ],
            ["aurora_code"=>"PUV508", "markup"=>47 ],
            ["aurora_code"=>"PUVP05", "markup"=>25 ],
            ["aurora_code"=>"PUV417", "markup"=>24 ],
            ["aurora_code"=>"PUV416", "markup"=>23 ],
            ["aurora_code"=>"PUV402", "markup"=>40 ],
            ["aurora_code"=>"PUV506", "markup"=>30 ],
            ["aurora_code"=>"PUVP12", "markup"=>25 ],
            ["aurora_code"=>"CUZ557", "markup"=>77 ],
            ["aurora_code"=>"CUZ446", "markup"=>50 ],
            ["aurora_code"=>"CUZ434", "markup"=>55 ],
            ["aurora_code"=>"CUZ441", "markup"=>55 ],
            ["aurora_code"=>"CUZ573", "markup"=>20 ],
            ["aurora_code"=>"CUZ574", "markup"=>20 ],
            ["aurora_code"=>"CUZ575", "markup"=>20 ],
            ["aurora_code"=>"CUZ528", "markup"=>27 ],
            ["aurora_code"=>"CUZ418", "markup"=>30 ],
            ["aurora_code"=>"CUZ5P6", "markup"=>20 ],
            ["aurora_code"=>"CUZ5P7", "markup"=>24 ],
            ["aurora_code"=>"CUZ438", "markup"=>20 ],
            ["aurora_code"=>"CUZ414", "markup"=>30 ],
            ["aurora_code"=>"CUZXTP", "markup"=>20 ],
            ["aurora_code"=>"UR1X13", "markup"=>20 ],
            ["aurora_code"=>"BCOMP3", "markup"=>1 ],
            ["aurora_code"=>"BCOMP2", "markup"=>1 ],
            ["aurora_code"=>"BCOMP1", "markup"=>1 ],
        ];

        MarkupService::where('period',$year)->where('client_id',16980)->forceDelete();

        $service_markups_data = [];

        foreach ($services as $service)
        {
            foreach ($service_markups as $service_markup)
            {
                if ($service_markup["aurora_code"] == $service["aurora_code"])
                {
                    array_push($service_markups_data,[
                        'period'=>$year,
                        'markup'=>$service_markup["markup"],
                        'service_id'=>$service["id"],
                        'client_id' =>16980,
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);
                }
            }
        }

        MarkupService::insert($service_markups_data);
    }
}
