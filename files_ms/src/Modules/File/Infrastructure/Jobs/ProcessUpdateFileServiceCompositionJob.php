<?php

namespace Src\Modules\File\Infrastructure\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;

class ProcessUpdateFileServiceCompositionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $all;

    /**
     * Create a new job instance.
     */
    public function __construct(bool $all = false)
    {
        $this->all = $all;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $stela_services_compositions = $this->getServicesStela();

        $fileServiceCompositionEloquentModel = FileServiceCompositionEloquentModel::select('id', 'code' , 'requires_confirmation');

        if($this->all == false)
        {
            $fileServiceCompositionEloquentModel->whereNull('requires_confirmation');
        }

        $fileServiceCompositionEloquentModel->chunk( 5000, function($service_compositions) use ($stela_services_compositions){

            dispatch(new ProcessUpdateFileServiceCompositionActionJob($service_compositions, $stela_services_compositions));

        });
    }

    public function getServicesStela()
    {        
        $stella= new ApiGatewayExternal();
        $stela_services_compositions = (array) $stella->search_file_service_compositions_requires_confirmation([], 'all', true, [], true);
        $results = [];
        foreach($stela_services_compositions as $composition)
        {
            array_push($results, $composition->codigo);
        }

        return $results;
    }

}
