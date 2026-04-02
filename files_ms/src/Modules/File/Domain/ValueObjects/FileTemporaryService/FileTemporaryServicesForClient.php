<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;
 
use Src\Modules\File\Domain\ValueObjects\File\FileTemporaryServices;
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileTemporaryServicesForClient extends ValueObjectArray
{
    public readonly FileTemporaryServices $itinerary_temporary_services;

    public function __construct(array $file, FileTemporaryServices $itinerary_temporary_services, array $params )
    {
        parent::__construct($file);    
        $this->itinerary_temporary_services = $this->parser($file,  $itinerary_temporary_services,  $params);
    }

    /**
     * @return FileTemporaryServices
     */
    public function parser($file, $itinerary_temporary_services,  $params ): FileTemporaryServices
    {   
         
        foreach($itinerary_temporary_services as $itinerary_temporary_service){
  
            $totalVenta = ($itinerary_temporary_service->totalAmount->value() * ($file['markup_client'] / 100)) + $itinerary_temporary_service->totalAmount->value();
            $itinerary_temporary_service->totalAmount->setValue(number_format($totalVenta, 2, '.', ''));
            $itinerary_temporary_service->markupCreated->setValue($file['markup_client']); 
            $itinerary_temporary_service->dateIn->setValue($params['date']);
            $itinerary_temporary_service->dateOut->setValue($params['date']);
            $itinerary_temporary_service->totalAdults->setValue($file['adults']);
            $itinerary_temporary_service->totalChildren->setValue($file['children']);
            $itinerary_temporary_service->totalInfants->setValue($file['infants']);

            foreach($itinerary_temporary_service->services as $services){            
                $services->dateIn->setValue($params['date']);
                $services->dateOut->setValue($params['date']);

                foreach($services->compositions as $composition){    

                    $amountSale = ($composition->amountCost->value() * ($file['markup_client'] / 100)) + $composition->amountCost->value();
                    $composition->amountSale->setValue(number_format($amountSale, 2, '.', '') );

                    $amountSaleOrigin = ($composition->amountCostOrigin->value() * ($file['markup_client'] / 100)) + $composition->amountCostOrigin->value();
                    $composition->amountSaleOrigin->setValue(number_format($amountSaleOrigin, 2, '.', ''));

                    $composition->markupCreated->setValue($file['markup_client']); 

                    $composition->dateIn->setValue($params['date']);
                    $composition->dateOut->setValue($params['date']);
                    $composition->totalAdults->setValue($file['adults']);
                    $composition->totalChildren->setValue($file['children']);
                    $composition->totalInfants->setValue($file['infants']);

                    foreach($composition->units as $index => $unit){  

                        $amountSale = ($unit->amountCost->value() * ($file['markup_client'] / 100)) + $unit->amountCost->value();
                        $unit->amountSale->setValue(number_format($amountSale, 2, '.', ''));
    
                        $amountSaleOrigin = ($unit->amountCostOrigin->value() * ($file['markup_client'] / 100)) + $unit->amountCostOrigin->value();
                        $unit->amountSaleOrigin->setValue(number_format($amountSaleOrigin, 2, '.', ''));
                  
                        if (isset($file['passengers'])) { 
                            $accommodations = [];
                            foreach($file['passengers'] as $passenger) {                                                               
                                array_push($accommodations,[
                                    'file_service_unit_id' => null,
                                    'file_passenger_id' => $passenger['id']
                                ]);
                            }                               
                            $unit->accommodations->setValue($accommodations);
                        }

                    }
                }
            }

        }

        return $itinerary_temporary_services;

        // throw new FileItineraryCancelationException("services not found or canceled");     
    }
 
    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->itinerary_temporary_services->fileTemporaryServices;
    }
}
