<?php

namespace Src\Modules\File\Infrastructure\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;  

class ProcessUpdateFileServiceCompositionActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;    
 
    private array $stela_services_compositions;
    private $service_compositions;

    /**
     * Create a new job instance.
     */
    public function __construct($service_compositions, array $stela_services_compositions)
    {         
        $this->service_compositions = $service_compositions;
        $this->stela_services_compositions = $stela_services_compositions;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {    

        foreach ( $this->service_compositions as $composition ){

            $requires_confirmation = 'N';
            if(in_array($composition->code, $this->stela_services_compositions))
            {        
                $requires_confirmation = 'S';
            }

            DB::table('file_service_compositions')
            ->where('id', $composition->id)
            ->update(['requires_confirmation' => $requires_confirmation]);                                               
        }   
    }
 
    
}
