<?php

namespace App\Console\Commands;

use App\Service;
use App\ServiceChild;
use App\ServiceRateAssociation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateMarketsServicesVista extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:update_markets_vista';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar las tarifas de servicios agregando el mercado vista';

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
//        $serviceRateAssociations = ServiceRateAssociation::with([
//            'service_rate' => function ($query) {
//                $query->select(['id', 'name']);
//                $query->with([
//                    'service' => function ($query) {
//                        $query->select(['id', 'aurora_code']);
//                    }
//                ]);
//            }
//        ])->where('entity', 'market')
//            ->where('object_id', 4)
//            ->get(['id', 'service_rate_id', 'entity', 'object_id']);
//
//        foreach ($serviceRateAssociations as $association) {
//            $new_association1 = new ServiceRateAssociation();
//            $new_association1->service_rate_id = $association->service_rate_id;
//            $new_association1->entity = $association->entity;
//            $new_association1->object_id = 19; // VISTA CUSCO
//            $new_association1->except = 0;
//            $new_association1->save();
//
//            $new_association2 = new ServiceRateAssociation();
//            $new_association2->service_rate_id = $association->service_rate_id;
//            $new_association2->entity = $association->entity;
//            $new_association2->object_id = 20; // VISTA URUBAMBA
//            $new_association2->except = 0;
//            $new_association2->save();
//        }

    }
}
