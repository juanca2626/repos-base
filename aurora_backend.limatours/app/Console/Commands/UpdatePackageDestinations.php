<?php

namespace App\Console\Commands;

use App\Package;
use App\Services\UpdatePackageDestinationsService;
use Illuminate\Console\Command;

class UpdatePackageDestinations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:update_destinations {ids* : The id of the packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update package destinations based on hotels and services';

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
    public function handle(UpdatePackageDestinationsService $updatePackageDestinationsService)
    {
        $packageIds = $this->argument('ids');
        
        if(in_array('all', $packageIds)){
            $packageIds = Package::where('status', 1)->where(function($query) {
                $query->where('destinations','')
                    ->orWhereNull('destinations');
            })->pluck('id')->toArray();     
        }

        $progressbar = $this->output->createProgressBar(count($packageIds));

        foreach ($packageIds as $packageId) {
            $updatePackageDestinationsService->execute($packageId);
            $progressbar->advance();
        }

        $progressbar->finish();
    }
}
