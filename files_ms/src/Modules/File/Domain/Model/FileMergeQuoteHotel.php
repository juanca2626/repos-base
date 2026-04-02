<?php

namespace Src\Modules\File\Domain\Model;
use Src\Shared\Domain\Entity;
use Carbon\Carbon; 

class FileMergeQuoteHotel extends Entity
{
    public readonly array $hotels_add;
    public readonly array $hotels_canceled;
    public readonly array $hotels_modified;

    public function __construct(
        public readonly array $file, 
        public readonly array $quote_aurora,
        public readonly array $passengers
    ) {
        $this->hotels_add = $this->hotel_news($file['client_id'], $quote_aurora['itineraries'], $passengers);
        $this->hotels_canceled = $this->hotel_cancels($file['itineraries'], $quote_aurora['itineraries']);
        $this->hotels_modified = $this->hotel_modified($file['client_id'], $file['itineraries'], $quote_aurora['itineraries'], $passengers );       
    }

    public function hotel_news($client_id, $itineraries, $passengers ): array
    {
        $hotelsAdd = [];

        foreach($itineraries as $ix => $itinerary){
           
            if($itinerary['type'] == 'hotel'){  

                if(!$itinerary['file_itinerary_id']){
                        
                    array_push($hotelsAdd, $this->format_hotel_reservation($client_id, $itinerary, $passengers ));
                } 
            }
        }

        return $hotelsAdd;
    }

    public function hotel_cancels($file_itineraries, $quote_itineraries): array
    {
        $canceledHotels = []; 

        foreach($file_itineraries as $ix => $itinerary){
            
            if($itinerary['entity'] == 'hotel'){  

                $quote_itineraries_search = collect($quote_itineraries)->filter(function ($result) use ($itinerary){
                    return $result['file_itinerary_id'] === $itinerary['id'];
                })->first(); 

                if(!$quote_itineraries_search){
                    array_push($canceledHotels, $this->format_hotel_cancelation($itinerary));
                }
                
            }

        }

        return $canceledHotels;
    }

    public function hotel_modified($client_id, $file_itineraries, $quote_itineraries, $passengers): array
    {

        $hotelsModified = []; 

        foreach($file_itineraries as $ix => $itinerary){
            
            if($itinerary['entity'] == 'hotel'){  
                $hotel_file = $itinerary['object_code'] ;
                $date_in_file = $itinerary['date_in'] ;
                $date_out_file = $itinerary['date_out'] ;
                $total_pax = $itinerary['total_adults'] + $itinerary['total_children'] ;
                $search = false;

                $quote_itineraries_search = collect($quote_itineraries)->filter(function ($result) use ($itinerary){
                    return $result['file_itinerary_id'] === $itinerary['id'];
                })->first(); 

                if($quote_itineraries_search  and !$quote_itineraries_search['locked']){

                    $hotel_quote = collect($quote_itineraries_search['hotel']->channel)->filter(function ($result){
                        return $result->channel_id === 1;
                    })->first()->code; 

                    $total_pax_sum = collect($quote_itineraries_search['rooms'])->sum(function ($result){
                        return $result['adult'] + $result['child'] ;
                    }); 

                    $date_in_quote = Carbon::createFromFormat('d/m/Y', $quote_itineraries_search['date_in'])->format('Y-m-d'); 
                    $date_out_quote = Carbon::createFromFormat('d/m/Y', $quote_itineraries_search['date_out'])->format('Y-m-d');
                    
                    // si cambiaron las fechas se tiene que anular la reserva y generar una nueva
                    if(( $date_in_file !== $date_in_quote or $date_out_file !== $date_out_quote) or ($total_pax != $total_pax_sum) or ($hotel_file != $hotel_quote)){
                        array_push($hotelsModified, [
                            'from' => $this->format_hotel_cancelation($itinerary),
                            'to' => $this->format_hotel_reservation($client_id, $quote_itineraries_search, $passengers)
                        ]);
                    }                  
                }
                    
            }

        }

        return $hotelsModified;
        
    }

    public function format_hotel_reservation($client_id, $itinerary, $passengers ): array
    {        
        $rooms = [];
        $hotel_channel = collect($itinerary['hotel']->channel)->filter(function ($result){
            return $result->channel_id === 1;
        })->first()->code;

        foreach($itinerary['rooms'] as $room){ 
            if($room['single']>0 or $room['double']>0 or $room['triple']>0){
                $service_rooms = $room['service_rooms'][0];                     
                $room_name = collect($service_rooms->rate_plan_room->room->translations)->filter(function ($result){
                    return $result->slug === 'room_name';
                })->first();
                $room_name = $room_name ? $room_name->value : '';
                array_push($rooms, [
                    'date_in' => $room['date_in'],
                    'date_out' => $room['date_out'], 
                    'room_name' =>$room_name,
                    'num_adult' => $room['adult'],
                    'num_child' => $room['child'],
                    'rate_plan_room_id' => $service_rooms->rate_plan_room_id,
                    'search_aurora' => $this->search_aurora_format($client_id, $hotel_channel, $itinerary, $room)
                ]);
            }
        }

        return  [        
            'hotel_name' => $itinerary['hotel']->name,            
            'typeclass' => [ 
                'name' => $itinerary['hotel']->typeclass->translations[0]->value,
                'color' => $itinerary['hotel']->typeclass->color,
            ],  
            'date_in' => $itinerary['date_in'], 
            'date_out' => $itinerary['date_out'],
            'passenger' => $passengers,
            'rooms' => $rooms 

        ];        
    }

    public function search_aurora_format($client_id,$hotel_channel, $itinerary, $room): array
    {     
        $ages_child = [];
        foreach($room['passengers'] as $passenger)
        {
            if($passenger->type == 'CHD')
            {
                array_push($ages_child, [
                    "age" => $passenger->age,
                    "child" => 1
                ]);
            }
        }

        return [
            'client_id' => $client_id,
            'destiny' => [
                'code' => $itinerary['hotel']->country->iso.','.$itinerary['hotel']->state->iso,
                'label' => $itinerary['hotel']->country->translations[0]->value.','.$itinerary['hotel']->state->translations[0]->value,
            ],
            "typeclass_id" => $itinerary['hotel']->typeclass->id,
            "hotels_search_code" =>  $hotel_channel,
            "date_from" => Carbon::createFromFormat('d/m/Y', $itinerary['date_in'])->format('Y-m-d'),
            "date_to" =>  Carbon::createFromFormat('d/m/Y', $itinerary['date_out'])->format('Y-m-d'),
            "quantity_rooms" => 1,   
            "quantity_persons_rooms" => [
                [
                    "adults" => $room['adult'],
                    "child" => $room['child'],
                    "ages_child" => $ages_child,            
                    "room" => 1
                ]
            ],
            "lang" => "en"  
        ];
    

    }

    public function format_hotel_cancelation($itinerary): array
    {
   
        $rooms = [];
        $cancel_in_file = [];
        $cancel_in_file_format = [];
        foreach($itinerary['rooms'] as $room){    
            $cancel_in_file[$room['file_room_id']][] = $room['file_room_unit_id'];                              
            array_push($rooms, [
                'date_in' => $itinerary['date_in'],
                'date_out' => $itinerary['date_out'], 
                'room_name' =>$room['room_name'],
                'num_adult' => $room['total_adults'],
                'num_child' => $room['total_children'] 
            ]);
        }

        foreach($cancel_in_file as $room => $units){
            array_push($cancel_in_file_format, [
                'id' =>  $room,
                'units' => implode(",", $units)
            ]);
        }

        return  [ 
            'file_itinerary' => $itinerary['id'],
            'hotel_id' => $itinerary['object_id'],
            'hotel_code' => $itinerary['object_code'],
            'hotel_name' => $itinerary['name'],                       
            'date_in' => Carbon::parse($itinerary['date_in'])->format('d/m/Y') , 
            'date_out' => Carbon::parse($itinerary['date_out'])->format('d/m/Y') ,
            'rooms' => $rooms,
            'cancel_in_file' => [
                'rooms' => $cancel_in_file_format
            ]
        ];
 
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'add' => $this->hotels_add,
            'canceled' => $this->hotels_canceled,
            'modified' => $this->hotels_modified, 
        ];
    }

}
