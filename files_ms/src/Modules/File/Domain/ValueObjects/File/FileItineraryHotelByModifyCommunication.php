<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\FileItineraryCancelationException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryHotelByModifyCommunication extends ValueObjectArray
{
    public readonly array $file;

    public function __construct(array $file, array $params)
    {
        parent::__construct($file);            
        $this->file = $this->parser($file, $params['file_itinerary_id'] ,$params['reservation_delete'], $params['send_communication_file_itinerary_id']);
    }

    /**
     * @return array
     */
    public function parser($file, $file_itinerary_id_delete, $reservation_delete, $fileItineraryId): array
    {
        $data = $file; 
        $itinerarySearch = null; 
        $rowOld = null;
        foreach($data["itineraries"] as $itinerary){
 
            if($itinerary["entity"] == "hotel"){

                if($itinerary["id"] == $file_itinerary_id_delete){
                    $rowOld = $this->getReservationActive($itinerary, $reservation_delete);
                }

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

        if($rowOld === null)
        { 
            throw new FileItineraryCancelationException("the itinerary id does not exist");   
        }

        // if(count($rowOld['rooms']) == 0)
        // {      
        //     throw new FileItineraryCancelationException("No rooms were found to delete");   
        // }

        if($itinerarySearch === null)
        {
            throw new FileItineraryCancelationException("there are no new rooms");  
        }

                   
        $data['itineraries'] = $itinerarySearch;  

        if(($data['itineraries']['date_in'] != $rowOld['date_in']) and count($rowOld['rooms'])>0){
                            
            $data['itineraries']['rooms_new'] = $data['itineraries']['rooms'];
            $data['itineraries']['rooms_new_date_in'] = $data['itineraries']['date_in'];
            $data['itineraries']['rooms_new_date_out'] = $data['itineraries']['date_out'];
            $data['itineraries']['rooms_new_start_time'] = $data['itineraries']['start_time'];
            $data['itineraries']['rooms_new_departure_time'] = $data['itineraries']['departure_time'];   
            $data['itineraries']['rooms_new_nights'] = $data['itineraries']['nights'];  
            
            $data['itineraries']['rooms'] = $rowOld['rooms'];
            $data['itineraries']['date_in'] = $rowOld['date_in'];
            $data['itineraries']['date_out'] = $rowOld['date_out'];
            $data['itineraries']['start_time'] = $rowOld['start_time'];
            $data['itineraries']['departure_time'] = $rowOld['departure_time'];
            $data['itineraries']['departure_time'] = $rowOld['departure_time'];
            $data['itineraries']['nights'] = $data['itineraries']['nights'];

        }else{
            $data['itineraries']['rooms'] = array_merge($rowOld['rooms'], $data['itineraries']['rooms']);            
        }

        unset($data['passengers']);
        unset($data['vips']);
        unset($data['serviceAmountLogs']);
        return $data;
               
    }

    public function getReservationActive(array $itinerary, array $reservation_delete = []): array
    {
        $delete_rooms = $reservation_delete; 
        $rooms = [];
        foreach($itinerary['rooms'] as $room)
        {                                 
            $amount_cost = 0;
            $amount_sale = 0;
            $occupantADL = 0;
            $occupantCHD = 0;
            
            $units = $this->filtrar_units($room, $delete_rooms);
            
            if(count($units)>0)
            {
                $amount_cost = 0;
                $amount_sale = 0;
                $occupantADL = 0;
                $occupantCHD = 0;
                foreach($units as $id => $unit){
                    $amount_cost = $amount_cost +  $unit['amount_cost'];
                    $amount_sale = $amount_sale +  $unit['amount_sale'];  
                    $occupantADL = $occupantADL + $unit['adult_num'];
                    $occupantCHD = $occupantCHD + $unit['child_num'];
                }

                $extructure = [
                    'date_in' => $itinerary['date_in'],
                    'date_out' => $itinerary['date_out'],
                    'channel_id' => $room['channel_id'],
                    'channel_reservation_code_master' => '',
                    'room_name' => $room['room_name'],
                    'total_rooms' =>  count($units),
                    'total_adults' =>  $occupantADL,
                    'total_children' =>  $occupantCHD,
                    'occupants' =>  $occupantADL + $occupantCHD,
                    'amount_cost' =>  $amount_cost,
                    'amount_sale' =>  $amount_sale,
                    'rate_plan_name' => $room['rate_plan_name'],
                    'rate_plan_code' => $room['rate_plan_code'], 
                    'confirmation_status' => $room['confirmation_status'],
                    'unit' => $units
                ];

                array_push($rooms, $extructure);
            }
            
        }
  
        return [
            'date_in' => $itinerary['date_in'],
            'date_out' => $itinerary['date_out'],
            'start_time' => $itinerary['start_time'],
            'departure_time' => $itinerary['departure_time'],
            'nights' => $this->nights($itinerary['date_in'],$itinerary['date_out']),
            'rooms' => $rooms
        ];
    }

    public function nights($dateIn, $dateOut): int
    {
        $date1 = new \DateTime($dateIn);
        $date2 = new \DateTime($dateOut);
        $diff = $date1->diff($date2);

        return $diff->days;
    }
    
    /* Devolvemos solo los units que no han sido eliminados */
    public function filtrar_units($room, array $delete_rooms): array
    {   
        $unitsFiltrados = [];
        foreach($room['units'] as $id => $unit){
            
            $encontro = false;
            foreach($delete_rooms as $deleteRoom)
            {
                if($room['id'] == $deleteRoom['id'])
                {
                    $encontro = false;
                    foreach($deleteRoom['units'] as $deletUnits)
                    {
                        if($unit['id'] == $deletUnits)
                        {
                            $encontro = true;
                        }
                    }
                    
                } 
            }       
            if($encontro == false)
            {
                array_push($unitsFiltrados, $unit);
            }  
        }
        return $unitsFiltrados;
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
