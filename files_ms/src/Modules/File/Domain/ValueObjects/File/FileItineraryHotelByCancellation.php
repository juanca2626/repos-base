<?php

namespace Src\Modules\File\Domain\ValueObjects\File;
use Carbon\Carbon;
use Src\Modules\File\Domain\Exceptions\FileItineraryCancelationException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryHotelByCancellation extends ValueObjectArray
{
    public readonly array $file;

    public function __construct(array $file, int $fileItineraryId, array $params, bool $validate_status)
    {
        parent::__construct($file);    
        $this->file = $this->parser($file, $fileItineraryId, $params, $validate_status);
    }

    /**
     * @return array
     */
    public function parser($file, $fileItineraryId ,$params, $validate_status): array
    {
        $data = $file;

        // $itinerarySearch = [];
        $stelaRooms = [];
        $hypperguestRooms = [];
        $itinerarySearch = null;
        $isHyperguest = false;
        foreach($data["itineraries"] as $itinerary){
 
            if($validate_status == true and $itinerary["status"] != "1"){
                continue;
            }
    
            $confirmationCode = "";
            if($itinerary["entity"] == "hotel"){



                if($itinerary["id"] == $fileItineraryId){  // filtramos el itinerario que estamos cancelando.
                   
                    // dd($itinerary,$fileItineraryId);

                    $rooms = [];
                    //Tenemos que filtrar las habitaciones que no estan pasando por parametros
                    foreach( $itinerary['rooms'] as $room){                
                        if($validate_status == true and $room["status"] != "1"){
                            continue;
                        }

                        // necesitamos armar las habitaciones que son de hypergues para agruparlas y obligarlas que se tienen que anular todas.                        
                        if($room["channel_id"] == "6"){ // es hyperguest
                                                  
                            if(trim($room['channel_reservation_code_master'])){                                                               
                                $hypperguestRooms[$room['id']] = [
                                    'code' => $room['channel_reservation_code_master'],
                                    'rooms' => $room['units']
                                ];                              
                            }                            
                        }

                        foreach($params as $param){ // buscamos en los parametros que nos enviaron
                       
                            if($param["id"] == $room["id"]){ //filtrar las habitaciones

                                if($room["channel_id"] == "6" and trim($room['channel_reservation_code_master'])) // basta que haya 1 solo habitacion con hyperguest se debe de validar que todos las otras habitaciones se deben de anular
                                { 
                                    $isHyperguest = true;
                                }

                                $penalityTotal = 0;
                                $penalityTotalVenta = 0;
                                $occupants = 0;
                                $amount_cost = 0;
                                $units = [];   
                                        
                                foreach( $room['units'] as $unit){

                                    if($validate_status == true and $unit["status"] != "1"){ 
                                        continue;
                                    }

                                    foreach($param["units"] as $paramUnit){ //filtrar por units
                                   
                                        if($paramUnit == $unit["id"]){
                                            $occupants = $occupants +  (intval($unit['adult_num']) + intval($unit['child_num']));
                                            $penalityTotal = $penalityTotal + $unit['penalty']['penalty_cost'];
                                            $penalityTotalVenta = $penalityTotalVenta + $unit['penalty']['penalty_sale'];
                                            $amount_cost = $amount_cost + $unit['amount_cost'];
                                            array_push($units, $unit );
                                            $confirmationCode = $unit["confirmation_code"];
                                            array_push($stelaRooms, $unit['penalty']['penalty_info']);

                                            
                                        }
                                    }

                                }      
                              
                                if(count($units)>0){       //Si encontro units lo agregamos al arreglo de rooms                     
                                    $room['units'] = $units;  
                                    $room['total_rooms'] = count($units); 
                                    $room['amount_cost'] = $amount_cost;
                                    $room['occupants'] = $occupants;
                                    $room['penality_total'] = $penalityTotal;         
                                    $room['penality_total_sale'] = $penalityTotalVenta;   
                                    array_push($rooms, $room );
                                }
                             
                            }
                        }

                    }
                    
                    if(count($rooms)>0){ //Si encontro rooms lo agregamos al itinerario filtrado                  
                        $itinerary['rooms'] = $rooms;
                        $itinerary['confirmation_code'] = $confirmationCode;
                        $itinerarySearch = $itinerary;   
                        // array_push($itinerarySearch, $itinerary);
                    }

                }
            }

        }

        if($itinerarySearch !== null){
                
            $stela = [
                'datapla' => [
                    'numeroPedido' => $data['reservation_id'],
                    'codigoCliente' => $data['client_code'],
                    'tipoCliente' => 2,
                    'codigoEjecutiva' => $data['executive_code'],
                    'fechaRegistro' => Carbon::parse($itinerarySearch['date_in'])->format('d/m/Y'),
                    'nroref' => $data['file_number']
                ],
                'datahtl' => [
                    [
                        'codigoHotel' => $itinerarySearch['object_code'],
                        'channelUsado' => 'BREDOW',
                        'codigoConfirma' => $itinerarySearch['object_code'],
                        'reserva' => $stelaRooms
                    ]
                ]
            ];
 
            $itinerarySearch['stella'] = $stela;
            $data['itineraries'] = $itinerarySearch; 
            $data['hyperguest'] = null;
            unset($data['passengers']);
            unset($data['vips']);
            unset($data['serviceAmountLogs']);

            if($data['itineraries']['confirmation_status'] == false){
                throw new FileItineraryCancelationException("The reservation was not confirmed");
            }

            // validamos que se anulen todos los codigos de hyperguest.
            if($validate_status == true and $isHyperguest)
            {
                if(count($hypperguestRooms) == 0)
                {
                    throw new FileItineraryCancelationException("No Hypergues rates were found"); 
                }

                $channel_reservation_code = '';
                foreach($hypperguestRooms as $hyperguest_room_id => $hyperguest){
                    
                    $channel_reservation_code = $hyperguest['code'];
         
                    $searchAll = false;                    
                    foreach($data['itineraries']['rooms'] as $room)
                    {
                        if($room['id'] == $hyperguest_room_id){

                            
                            $searchAllRooms = [];
                            foreach($hyperguest['rooms'] as $room_hyperguest)
                            {                               
                                foreach($room['units'] as $unit)
                                {   
                                    if($room_hyperguest['id'] == $unit['id']){
                                        array_push($searchAllRooms, $unit['id']);
                                    }
                                }
                            }

                            if(count($hyperguest['rooms']) == count($searchAllRooms))
                            {
                                $searchAll = true;
                            }
                        }
                    }

                    if($searchAll == false)
                    {
                        throw new FileItineraryCancelationException("All rooms are cancelled if booked on HyperGuest."); 
                    }
                }

                $data['hyperguest'] = [
                    "url" => "/2.0/booking/cancel",
                    "method" => "POST",
                    "params" => [                    
                        "bookingId" => $channel_reservation_code,
                        "reason" => "Cancel Hyperguest",
                        "simulation" => false
                    ]
                ];
            }

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
