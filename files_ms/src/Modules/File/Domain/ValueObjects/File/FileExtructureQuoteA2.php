<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\FileItineraryCancelationException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray; 

final class FileExtructureQuoteA2 extends ValueObjectArray
{
    public readonly array $file; 
    public function __construct(array $file, array | null $clone_params = null)
    {
        parent::__construct($file);   
        if(is_array($clone_params)){
            $file['description'] = $clone_params['description'];
            $file['clone_file_id'] = $file['id'];
            $file['clone_parameters'] = $clone_params;
            $file['clone_executed'] = 0;
        } 

        $this->file = $this->parser($file);
    }

    /**
     * @return array
     */
    public function parser($file): array
    {
        $data = $file;       
        foreach($data["itineraries"] as $ix => $itinerary){
              
            if($itinerary["entity"] == "hotel"){
     
                $rooms = []; 

                foreach( $itinerary['rooms'] as $ix2 =>  $room){ 
                    $single = 0;
                    $double = 0;
                    $triple = 0;  
                    if($room['occupation'] == 1){
                        $single = 1;
                    }

                    if($room['occupation'] == 2){
                        $double = 1;
                    }

                    if($room['occupation'] == 3){
                        $triple = 1;
                    }

                    foreach( $room['units'] as $ix3 => $unit){   
                        $room['file_room_id'] = $room['id'];
                        $room['file_room_unit_id'] = $unit['id'];                     
                        $room['rate_plan_room_id'] = $unit['rates_plans_rooms_id'];
                        $room['single'] = $single;
                        $room['double'] = $double;
                        $room['triple'] = $triple;                        
                        $room['confirmation_status'] = $room['confirmation_status'];
                        $room['accommodations'] = $unit['accommodations'];
                        $room['total_adults'] = $unit['adult_num'];
                        $room['total_children'] = $unit['child_num'];
                        $room['amount_sale'] = $unit['amount_sale'];
                        $room['amount_cost'] = $unit['amount_cost'];
                        $room['itinerary_id'] = $itinerary['id'];
                        unset($room['file_room_amount']);
                        unset($room['file_room_amount_logs']);
                        unset($room['units']);                         
                        array_push($rooms, $room); 
                    }                          
                }

                
                $data["itineraries"][$ix]['rooms'] = $rooms;
           
            }

            if($itinerary["entity"] == "service"){
                $data["itineraries"][$ix]['accommodations'] = [];
                $compositions = isset($itinerary['services'][0]['compositions']) ? $itinerary['services'][0]['compositions'] : [];
                if(isset($compositions) and count($compositions)>0){
                    if(isset($compositions[0]['units']) and count($compositions[0]['units'])>0){
                        $accommodations = $itinerary['services'][0]['compositions'][0]['units'][0]['accommodations'];                    
                        $data["itineraries"][$ix]['accommodations'] = $accommodations;
                    }
                }

                // unset($data["itineraries"][$ix]['services']);
            }
        
        }

        return $data;     
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->file;
    }
}
