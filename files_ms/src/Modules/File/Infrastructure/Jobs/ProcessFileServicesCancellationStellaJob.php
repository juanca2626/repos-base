<?php

namespace Src\Modules\File\Infrastructure\Jobs;
 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCompositionSupplierEloquentModel; 
use Src\Modules\File\Presentation\Http\Traits\CalculateProfitability;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;

class ProcessFileServicesCancellationStellaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; 
    
    private object $newComponent; 
    private object $component; 
    private object $serviceEquivalence; 

    /**
     * Create a new job instance.
     */
    public function __construct($newComponent, $component, $serviceEquivalence)
    {
        $this->newComponent = $newComponent;
        $this->component = $component;
        $this->serviceEquivalence = $serviceEquivalence;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {          

        // file_put_contents("service_in_providers.txt", json_encode(["code_request_book" => $this->component->supplier->code_request_book, "persons" => ($this->newComponent->total_adults + $this->newComponent->total_children)]), FILE_APPEND);$newComponent, $component, $serviceEquivalence
        

        $cancellationPoliciesServices = [];

        if($this->component->supplier->code_request_book) {
            $aurora = new AuroraExternalApiService(); 

            $cancellationPoliciesServices = $aurora->searchCancellationPoliciesServicesSupplier([
                'supplier' => trim($this->component->supplier->code_request_book),
                'persons' =>  ($this->newComponent->total_adults + $this->newComponent->total_children)
            ]);

            if(!is_object($cancellationPoliciesServices) && !is_array($cancellationPoliciesServices))
            {
                $cancellationPoliciesServices = (object) json_decode($cancellationPoliciesServices);
            }
 
            if(isset($cancellationPoliciesServices->data)) {
                $cancellationPoliciesServices = $cancellationPoliciesServices->data;
            }
        }

        $newSupplier = new FileCompositionSupplierEloquentModel();
        $newSupplier->file_service_composition_id = $this->newComponent->id;
        $newSupplier->reservation_for_send = $this->component->supplier->reservation_for_send;
        $newSupplier->for_assign = $this->component->supplier->for_assign;
        $newSupplier->assigned = 0;
        $newSupplier->code_request_book = $this->component->supplier->code_request_book;
        $newSupplier->code_request_invoice = $this->component->supplier->code_request_invoice;
        $newSupplier->code_request_voucher = $this->component->supplier->code_request_voucher;
        $newSupplier->policies_cancellation_service = count($cancellationPoliciesServices)>0 ? json_encode($cancellationPoliciesServices) : $this->serviceEquivalence->policies_cancellation_service;
        $newSupplier->send_communication = @$this->component->supplier->send_communication;
        $newSupplier->save();


    }
 
}
