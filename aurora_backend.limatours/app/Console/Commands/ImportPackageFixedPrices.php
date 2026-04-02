<?php

namespace App\Console\Commands;

use App\ConfigMarkup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Hotel;
use App\Package;
use Illuminate\Support\Facades\DB;

class ImportPackageFixedPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:import_package_fixed_prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'copia el campo enable_fixed_prices en la tabla package_plan_rates';

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
        DB::transaction(function () {

            Package::with(['plan_rates'])->chunk( 5, function($packages){
                foreach($packages as $package){
                    if($package->enable_fixed_prices == "1"){
                        foreach($package->plan_rates as $rate){
                            $rate->enable_fixed_prices = "1";
                            $rate->save();
                        }
                    }
                }
            });
            
        });
    }
}
