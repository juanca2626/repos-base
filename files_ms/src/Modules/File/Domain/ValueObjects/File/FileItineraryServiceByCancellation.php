<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\FileItineraryCancelationException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryServiceByCancellation extends ValueObjectArray
{
    public readonly array $file;

    public function __construct(array $file, int $fileItineraryId, array $params)
    {
        parent::__construct($file);    
        $this->file = $this->parser($file, $fileItineraryId, $params);
    }

    /**
     * @return array
     */
    public function parser($file, $fileItineraryId ,$params): array
    {
        $data = $file;

        // $itinerarySearch = []; 
        $itinerarySearch = null;
        $hyperguest = [];
        foreach($data["itineraries"] as $itinerary){
 
            if($itinerary["status"] != "1"){
                continue;
            }
 
            if(in_array($itinerary["entity"], ['service', 'service-temporary'])){
                                
                if($itinerary["id"] == $fileItineraryId){  // filtramos el itinerario que estamos cancelando.

                    $services = [];
                    // dd($itinerary['services']);
                    //Tenemos que filtrar los servicios que no estan pasando por parametros
                    foreach( $itinerary['services'] as $service){ 

                        if($service["status"] != "1"){
                            continue;
                        }

                        foreach($params as $param){ // buscamos en los parametros que nos enviaron
                       
                            if($param["id"] == $service["id"]){ //filtrar las habitaciones
                                                               
                         
                                $amount_cost = 0;
                                $compositions = [];            
                                foreach( $service['compositions'] as $composition){

                                    if($composition["status"] != "1"){ 
                                        continue;
                                    }

                                    foreach($param["compositions"] as $paramComposition){ //filtrar por compositions

                                        if($paramComposition == $composition["id"]){ 
                                            $amount_cost = $amount_cost + $composition['amount_cost'];

                                            $units = [];
                                            foreach($composition['units'] as $unit){
                                                unset($unit['accommodations']);
                                                array_push($units, $unit);
                                            }

                                            

                                            $composition['units'] = $units;
                                            $composition['supplier'] = $composition['supplier'];
                                            array_push($compositions, $composition); 
                                        }
                                    }

                                }      

                                if(count($compositions)>0){       //Si encontro compositions lo agregamos al arreglo de rooms
                                    $service['compositions'] = $compositions;  
                                    $service['total_compositions'] = count($compositions); 
                                    $service['amount_cost'] = $amount_cost;        
                                    array_push($services, $service );
                                }
                             
                            }
                        }
                    }
                    
                    if(count($services)>0){ //Si encontro services lo agregamos al itinerario filtrado
                        unset($itinerary['policies_cancellation_service']);
                        $itinerary['services'] = $services;                         
                        $itinerarySearch = $itinerary;   
                        // array_push($itinerarySearch, $itinerary);
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

        throw new FileItineraryCancelationException("services not found or canceled");     
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->file;
    }
}
