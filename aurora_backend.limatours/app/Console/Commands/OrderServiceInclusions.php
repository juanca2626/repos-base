<?php

namespace App\Console\Commands;

use App\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OrderServiceInclusions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:order_inclusions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear orden para las inclusiones de los servicios';

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
        $services = DB::table('services')->whereNull('deleted_at')->get();

        foreach ($services as $service)
        {
            $order= 1;
            $service_inclusions = DB::table('service_inclusions')->where('service_id',$service->id)->orderBy('day')->get();

            foreach ($service_inclusions as $service_inclusion)
            {
                DB::table('service_inclusions')->where('id',$service_inclusion->id)->update([
                    'order'=>$order
                ]);
                $order++;
            }
        }
    }
}
