<?php

namespace Src\Modules\File\Infrastructure\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelSupplierEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;

class ProcessFileHotelSupplierStellaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private ApiGatewayExternal $aurora;
    private string $fileId;
    private string $formatDate = 'd/m/Y';
    private array $hotelCodes;

    /**
     * Create a new job instance.
     */
    public function __construct(string $fileId, array $hotelCodes)
    {        
        $this->aurora = new ApiGatewayExternal();
        $this->fileId = $fileId;
        $this->hotelCodes = $hotelCodes;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // file_put_contents("hotels_in.txt", json_encode($this->hotelCodes));
        $hotelSuppliers = $this->aurora->getHotelSuppliers($this->hotelCodes);
        $fileItineraries = FileItineraryEloquentModel::where('file_id', $this->fileId)
            ->where('entity', 'hotel')
            ->get();

        DB::transaction(function () use ($hotelSuppliers, $fileItineraries) {
            foreach ($fileItineraries as $fileItinerary) {
                foreach ($hotelSuppliers as $hotelSupplier) {
                    if ($fileItinerary->object_code == $hotelSupplier->code){
                        $this->saveSupplier($fileItinerary, $hotelSupplier);
                        break;
                    }
                }
            }

        });
    }


    public function saveSupplier($fileItinerary, $component): void
    {
        $newSupplier = new FileHotelSupplierEloquentModel();
        $newSupplier->file_itinerary_id = $fileItinerary->id;
        $newSupplier->reservation_for_send = $component->reservation_for_send;
        $newSupplier->for_assign = $component->for_assign;
        $newSupplier->assigned = 0;
        $newSupplier->code_request_book = $component->code_request_book;
        $newSupplier->code_request_invoice = $component->code_request_invoice;
        $newSupplier->code_request_voucher = $component->code_request_voucher;
        $newSupplier->save();
    }
}
