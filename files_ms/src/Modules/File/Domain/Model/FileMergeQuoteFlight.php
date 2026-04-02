<?php

namespace Src\Modules\File\Domain\Model;
use Src\Shared\Domain\Entity;
use Carbon\Carbon; 

class FileMergeQuoteFlight extends Entity
{
    public readonly array $flights_add;
    public readonly array $flights_canceled;
    public readonly array $flights_modified;

    public function __construct(
        public readonly array $file, 
        public readonly array $quote_aurora,
        public readonly array $passengers
    ) {
        $this->flights_add = $this->flight_news($quote_aurora['itineraries']);
        $this->flights_canceled = $this->flight_cancels($file['itineraries'], $quote_aurora['itineraries']);
        $this->flights_modified = $this->flight_modified($file['itineraries'], $quote_aurora['itineraries']);   
    }

    public function flight_news($itineraries): array
    {
        $flightsAdd = [];

        foreach($itineraries as $ix => $itinerary){
           
            if($itinerary['type'] == 'flight'){  

                if(!$itinerary['file_itinerary_id']){
                        
                    array_push($flightsAdd, $this->format_flight_reservation($itinerary));
                } 
            }
        }

        return $flightsAdd;
    }

    public function flight_cancels($file_itineraries, $quote_itineraries): array
    {
        $canceledFlights = []; 

        foreach($file_itineraries as $ix => $itinerary){
            
            if($itinerary['entity'] == 'flight'){  

                $quote_itineraries_search = collect($quote_itineraries)->filter(function ($result) use ($itinerary){
                    return $result['file_itinerary_id'] === $itinerary['id'];
                })->first(); 

                if(!$quote_itineraries_search){
                    array_push($canceledFlights, $this->format_flight_cancelation($itinerary));
                }
                
            }

        }

        return $canceledFlights;
    }

    public function flight_modified($file_itineraries, $quote_itineraries): array
    {

        $flightsModified = []; 

        foreach($file_itineraries as $ix => $itinerary){
            
            if($itinerary['entity'] == 'flight'){  
                $flight_file = $itinerary['object_code'] ;
                $date_in_file = $itinerary['date_in'] ; 
                $total_pax = $itinerary['total_adults'] + $itinerary['total_children'] ;
             
                $quote_itineraries_search = collect($quote_itineraries)->filter(function ($result) use ($itinerary){
                    return $result['file_itinerary_id'] === $itinerary['id'];
                })->first(); 

                if($quote_itineraries_search  and !$quote_itineraries_search['locked']){
 
                    $total_pax_sum =  $quote_itineraries_search['adult']  + $quote_itineraries_search['child'];
                    $date_in_quote = Carbon::createFromFormat('d/m/Y', $quote_itineraries_search['date_in'])->format('Y-m-d');  
                    
                    // si cambiaron las fechas se tiene que anular la reserva y generar una nueva
                    if(( $date_in_file != $date_in_quote) or ($total_pax != $total_pax_sum) ){
                        array_push($flightsModified, [ 
                            'from' => $this->format_flight_cancelation($itinerary),
                            'to' => $this->format_flight_reservation($quote_itineraries_search)
                        ]);
                    }                  
                }
                    
            }

        }

        return $flightsModified;
        
    }

    public function format_flight_reservation($itinerary): array
    {        
        return  [   
            'file_itinerary_id' => $itinerary['file_itinerary_id'], 
            'file_id' => $this->file['id'],   
            'type' => "flight",
            'id' => $itinerary['object_id'], 
            'code_flight' => $itinerary['code_flight'],  
            'origin' => $itinerary['origin'], 
            'destiny' => $itinerary['destiny'], 
            'date' => Carbon::createFromFormat('d/m/Y', $itinerary['date_in'])->format('Y-m-d'), 
            'adult_num' => $itinerary['adult'],
            'child_num' => $itinerary['child'], 
            'inf_num' => $itinerary['infant']
        ];        
    }
 
    public function format_flight_cancelation($itinerary): array
    {           
        return  [    
            'file_itinerary_id' => $itinerary['id'],     
            'flight_name' => $itinerary['name'],            
            'flight_code' => $itinerary['object_code'],   
            'origin' => $itinerary['city_in_iso'],
            'destiny' => $itinerary['city_out_iso'],
            'date_in' => $itinerary['date_in'], 
            'num_adult' => $itinerary['total_adults'],
            'num_child' => $itinerary['total_children']  
        ];  
 
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'add' => $this->flights_add,
            'canceled' => $this->flights_canceled,
            'modified' => $this->flights_modified, 
        ];
    }

}
