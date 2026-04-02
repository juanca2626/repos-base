<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\FileItineraryCancelationException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryHotelByNewCommunication extends ValueObjectArray
{
    public readonly array $file;

    public function __construct(array $file, int $fileItineraryId)
    {
        parent::__construct($file);    
        $this->file = $this->parser($file, $fileItineraryId);
    }

    /**
     * @return array
     */
    public function parser($file, $fileItineraryId): array
    {
        $data = $file;

        // $itinerarySearch = [];
        $stelaRooms = [];
        $hypperguestRooms = [];
        $itinerarySearch = null;
        $isHyperguest = false;
        foreach($data["itineraries"] as $itinerary){
 
            if($itinerary["entity"] == "hotel"){

                if($itinerary["id"] == $fileItineraryId){  // filtramos el itinerario que hemos agregado.
                           
                    $rooms = [];                 
                    foreach( $itinerary['rooms'] as $room){                
                        
                        $penalityTotal = 0;
                        $occupants = 0;
                        $amount_cost = 0;
                        $units = []; 

                        foreach( $room['units'] as $unit){
                       
                            $occupants = $occupants +  (intval($unit['adult_num']) + intval($unit['child_num']));
                            $penalityTotal = $penalityTotal + $unit['penalty']['penalty_cost'];
                            $amount_cost = $amount_cost + $unit['amount_cost'];
                            array_push($units, $unit );  
                        }      
                      
                        if(count($units)>0){       //Si encontro units lo agregamos al arreglo de rooms                     
                            $room['units'] = $units;  
                            $room['total_rooms'] = count($units); 
                            $room['amount_cost'] = $amount_cost;
                            $room['occupants'] = $occupants;        
                            array_push($rooms, $room );
                        }                        
                    }
                    
                    if(count($rooms)>0){ //Si encontro rooms lo agregamos al itinerario filtrado                  
                        $itinerary['rooms'] = $rooms; 
                        $itinerarySearch = $itinerary;    
                    }

                }
            }

        }

        if($itinerarySearch !== null){                     
            $data['itineraries'] = $itinerarySearch;  
            unset($data['passengers']);
            unset($data['vips']);
            unset($data['serviceAmountLogs']);
            return $data;
        }

        throw new FileItineraryCancelationException("rooms not found or canceled");     
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->file;
    }

    public function dataHyperguest(){

    }
    
}
