<?php

namespace Src\Modules\File\Domain\Model;
use Src\Shared\Domain\Entity;
use Carbon\Carbon; 

class FileMergeQuoteService extends Entity
{
    public readonly array $services_add;
    public readonly array $services_canceled;
    public readonly array $services_modified;

    public function __construct(
        public readonly array $file, 
        public readonly array $quote_aurora,
        public readonly array $passengers
    ) {
        $this->services_add = $this->service_news($file['client_id'], $quote_aurora['itineraries'], $passengers);
        $this->services_canceled = $this->service_cancels($file['itineraries'], $quote_aurora['itineraries']);
        $this->services_modified = $this->service_modified($file['client_id'], $file['itineraries'], $quote_aurora['itineraries'], $passengers );   
    }

    public function service_news($client_id, $itineraries, $passengers ): array
    {
        $servicesAdd = [];

        foreach($itineraries as $ix => $itinerary){
           
            if($itinerary['type'] == 'service'){  

                if(!$itinerary['file_itinerary_id']){
                        
                    array_push($servicesAdd, $this->format_service_reservation($client_id, $itinerary, $passengers ));
                } 
            }
        }

        return $servicesAdd;
    }

    public function service_cancels($file_itineraries, $quote_itineraries): array
    {
        $canceledServices = []; 

        foreach($file_itineraries as $ix => $itinerary){
            
            if($itinerary['entity'] == 'service'){  

                $quote_itineraries_search = collect($quote_itineraries)->filter(function ($result) use ($itinerary){
                    return $result['file_itinerary_id'] === $itinerary['id'];
                })->first(); 

                if(!$quote_itineraries_search){
                    array_push($canceledServices, $this->format_service_cancelation($itinerary));
                }
                
            }

        }

        return $canceledServices;
    }

    public function service_modified($client_id, $file_itineraries, $quote_itineraries, $passengers): array
    {

        $servicesModified = []; 

        foreach($file_itineraries as $ix => $itinerary){
            
            if($itinerary['entity'] == 'service'){  
                $service_file = $itinerary['object_code'] ;
                $date_in_file = $itinerary['date_in'] ; 
                $total_pax = $itinerary['total_adults'] + $itinerary['total_children'] ;
             
                $quote_itineraries_search = collect($quote_itineraries)->filter(function ($result) use ($itinerary){
                    return $result['file_itinerary_id'] === $itinerary['id'];
                })->first(); 

                if($quote_itineraries_search  and !$quote_itineraries_search['locked']){
                    $service_quote = $quote_itineraries_search['service']->aurora_code;
                    $total_pax_sum =  $quote_itineraries_search['adult']  + $quote_itineraries_search['child'];
                    $date_in_quote = Carbon::createFromFormat('d/m/Y', $quote_itineraries_search['date_in'])->format('Y-m-d');  
                    
                    // si cambiaron las fechas se tiene que anular la reserva y generar una nueva
                    if(( $date_in_file != $date_in_quote) or ($total_pax != $total_pax_sum) or ($service_file != $service_quote) ){
                        array_push($servicesModified, [ 
                            'from' => $this->format_service_cancelation($itinerary),
                            'to' => $this->format_service_reservation($client_id, $quote_itineraries_search, $passengers)
                        ]);
                    }                  
                }
                    
            }

        }

        return $servicesModified;
        
    }

    public function format_service_reservation($client_id, $itinerary, $passengers ): array
    {        
        return  [       
            'service_code' => $itinerary['service']->aurora_code, 
            'service_name' => $itinerary['service']->service_translations[0]->name,            
            'service_type' => isset($itinerary['service_type']) ? $itinerary['service_type']->code : '', 
            'date_in' => $itinerary['date_in'],  
            'num_adult' => $itinerary['adult'],
            'num_child' => $itinerary['child'],  
            'quote_service_id' => $itinerary['service_rate']->quote_service_id,
            'service_rate_id' => $itinerary['service_rate']->service_rate_id,
            'search_aurora' => $this->search_aurora_format($client_id, $itinerary['service']->aurora_code, $itinerary),          
            'passenger' => $passengers  

        ];        
    }

    public function search_aurora_format($client_id,$service_code, $itinerary): array
    {     
        $ages_child = [];
        foreach($itinerary['passengers'] as $passenger)
        {
            if($passenger->passenger->type == 'CHD')
            {
                array_push($ages_child, [
                    "age" => $passenger->passenger->age_child->age
                ]);
            }
        }

        return [
            'lang' => 'en',
            'client_id' => $client_id,
            'destiny' => "",
            'origin' => "",
            "date" => $itinerary['date_in_format'],                                                
            "quantity_persons" => [
                [
                    "adults" => $itinerary['adult'],
                    "child" => $itinerary['child'],
                    "age_childs" => $ages_child 
                ]
            ],
            "type" => "all",
            "category" => "all",
            "experience" => "all",
            "classification" => "all",
            "filter" =>  $service_code, 
            "limit" => 50,
            "page" => 1
        ];
    

    }

    public function format_service_cancelation($itinerary): array
    {    
        $cancel_in_file = [];
        $cancel_in_file_format = [];
        foreach($itinerary['services'] as $service){    
            foreach($service['compositions'] as $composition){ 
                $cancel_in_file[$service['id']][] = $composition['id'];  
            }                                                 
        }

        foreach($cancel_in_file as $service_id => $compositions){
            array_push($cancel_in_file_format, [
                'id' =>  $service_id,
                'compositions' => implode(",", $compositions)
            ]);
        }

        return  [ 
            'file_itinerary' => $itinerary['id'], 
            'service_code' => $itinerary['object_code'],
            'service_name' => $itinerary['name'],                       
            'date_in' => $itinerary['date_in'], 
            'num_adult' => $itinerary['total_adults'],
            'num_child' => $itinerary['total_children'],               
            'cancel_in_file' => [
                'services' => $cancel_in_file_format
            ]
        ];
 
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'add' => $this->services_add,
            'canceled' => $this->services_canceled,
            'modified' => $this->services_modified, 
        ];
    }

}
