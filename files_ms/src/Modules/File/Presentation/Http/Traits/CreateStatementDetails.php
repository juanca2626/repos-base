<?php

namespace Src\Modules\File\Presentation\Http\Traits;

trait CreateStatementDetails
{
  
    public function details($file): array
    {

        $accommodations = [];

        if($file->suggested_accommodation_sgl > 0 or $file->suggested_accommodation_dbl > 0  or $file->suggested_accommodation_tpl > 0 ){
            $accommodations = [
                'sgl' => $file->suggested_accommodation_sgl,
                'dbl' => $file->suggested_accommodation_dbl,
                'tpl' => $file->suggested_accommodation_tpl,
            ];
        }

        if(count($accommodations) == 0)
        {
            $details = $this->getOtherDetails($file->total_amount, $file->adults, $file->children);
        }else{
            $details = $this->detailOccupations($file, $accommodations);
            
        }
        return $details;
    }
    
    public function detailOccupations($file, $accommodations): array
    {
        
        $itineraries = $file->itineraries;
        $total = $file->total_amount;
        $adults = $file->adults;
        $children = $file->children; 
        $pax = $adults + $children;

        $details = [];
        $rates = [];
        $validate = true;

        
        $imports = [
            'sgl' => [
                'price' => 0,                
                'adl_pax' => 0,
                'chd_pax' => 0,
            ],
            'dbl' => [
                'price' => 0,
                'adl_pax' => 0,
                'chd_pax' => 0,
            ],
            'tpl' => [
                'price' => 0,
                'adl_pax' => 0,
                'chd_pax' => 0,
            ],
        ];


        foreach($itineraries as $itinerary){
            if( $itinerary->entity == 'hotel'){

                if(!in_array($itinerary->rate_plan_code, $rates)){
                    array_push($rates, $itinerary->rate_plan_code);
                }

                $accommodation_itinerary = [
                    'sgl' => 0,
                    'dbl' => 0,
                    'tpl' => 0,
                ];

                foreach($itinerary->rooms as $room)
                {
                    $pax = ($room->total_adults + $room->total_children);

                    if($room->occupation == 1){                        
                        $accommodation_itinerary['sgl'] = $room->total_rooms;
                        $imports['sgl']['price'] = $imports['sgl']['price'] + ($room->total_amount / $pax);                        
                        $imports['sgl']['adl_pax'] = $room->total_adults;
                        $imports['sgl']['chd_pax'] = $room->total_children;
                    }

                    if($room->occupation == 2){                        
                        $accommodation_itinerary['dbl'] = $room->total_rooms;            
                        $imports['dbl']['price'] = $imports['dbl']['price'] + ($room->total_amount / $pax);                        
                        $imports['dbl']['adl_pax'] = $room->total_adults;
                        $imports['dbl']['chd_pax'] = $room->total_children;      
                    }

                    if($room->occupation == 3){
                        $accommodation_itinerary['tpl'] = $room->total_rooms; 
                        $imports['tpl']['price'] = $imports['tpl']['price'] + ($room->total_amount / $pax);                        
                        $imports['tpl']['adl_pax'] = $room->total_adults;
                        $imports['tpl']['chd_pax'] = $room->total_children;               
                    }               
                }
 
                if( ($accommodations['sgl'] != $accommodation_itinerary['sgl']) or ($accommodations['dbl'] != $accommodation_itinerary['dbl']) or ($accommodations['tpl'] != $accommodation_itinerary['tpl']) )
                {                    
                    $validate = false;
                }
            } 
        } 

 
        $t = [];
        foreach($itineraries as $itinerary){
                           
            if( $itinerary->entity != 'flight' and $itinerary->entity != 'hotel')
            {
                
                $import_service = ($itinerary->total_amount /  ($itinerary->total_adults +  $itinerary->total_children) );
    
                if($accommodations['sgl']>0){ 
                    $imports['sgl']['price'] = $imports['sgl']['price'] + $import_service;  
                    
                }
                if($accommodations['dbl']>0){  
                    $imports['dbl']['price'] = $imports['dbl']['price'] + $import_service;  
                    
                }
                if($accommodations['tpl']>0){ 
                    $imports['tpl']['price'] = $imports['tpl']['price'] + $import_service;  
                    
                }
            }    
        } 
        
 
        // Si la tarifa es diferenciada osea son distintas ya no aplica  calculo por ocupacion y devuelve el calculo general
        if(count($rates)>1){
            $this->getOtherDetails($total, $adults, $children);
        }

        // si la validacion falla ya no aplica  calculo por ocupacion y devuelve el calculo general
        if($validate == false){
            $this->getOtherDetails($total, $adults, $children);
        }


        $results = [];
        $total = 0;
        if($accommodations['sgl']>0)
        {

            if($imports['sgl']['adl_pax'] > 0){
                $quantity = $imports['sgl']['adl_pax'];
                $unit_price = number_format($imports['sgl']['price'], 2, '.', '');                
                $amount = number_format($quantity * $unit_price, 2, '.', '')   ;                
                
                array_push($results,[
                    'description' => 'PRECIO NETO POR PERSONA-ADULTO - HAB SIMPLE',
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'amount' => $amount,
                    'type_room' => 'SGL',
                    'type_pax' => 'ADL'
                ]);

                $total = $total + $amount;
            }

            if($imports['sgl']['chd_pax'] > 0){ 
                $quantity = $imports['sgl']['chd_pax'];
                $unit_price = number_format($imports['sgl']['price'], 2, '.', '');                
                $amount = number_format($quantity * $unit_price, 2, '.', '')   ;   

                array_push($results,[
                    'description' => 'PRECIO NETO POR PERSONA-NIÑO - HAB SIMPLE',
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'amount' => $amount,
                    'type_room' => 'SGL',
                    'type_pax' => 'CHD'
                ]);

                $total = $total + $amount;
            }

        }

        if($accommodations['dbl']>0)
        {

            if($imports['dbl']['adl_pax'] > 0){               
                $quantity = $imports['dbl']['adl_pax'];
                $unit_price = number_format($imports['dbl']['price'], 2, '.', '');                
                $amount = number_format($quantity * $unit_price, 2, '.', '')   ;   

                array_push($results,[
                    'description' => 'PRECIO NETO POR PERSONA-ADULTO - HAB DOBLE',
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'amount' => $amount,
                    'type_room' => 'DBL',
                    'type_pax' => 'ADL'
                ]);

                $total = $total + $amount;
            }

            if($imports['dbl']['chd_pax'] > 0){ 
                $quantity = $imports['dbl']['chd_pax'];

                $unit_price = number_format($imports['dbl']['price'], 2, '.', '');                
                $amount = number_format($quantity * $unit_price, 2, '.', '')   ;   

                array_push($results,[
                    'description' => 'PRECIO NETO POR PERSONA-NIÑO - HAB DOBLE',
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'amount' => $amount,
                    'type_room' => 'DBL',
                    'type_pax' => 'CHD'
                ]);

                $total = $total + $amount;
            }

        }

        if($accommodations['tpl']>0)
        {

            if($imports['tpl']['adl_pax'] > 0){      

                $quantity = $imports['tpl']['adl_pax'];
                $unit_price = number_format($imports['tpl']['price'], 2, '.', '');                
                $amount = number_format($quantity * $unit_price, 2, '.', '')   ;   

                array_push($results,[
                    'description' => 'PRECIO NETO POR PERSONA-ADULTO - HAB TRIPLE',
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'amount' => $amount,
                    'type_room' => 'TPL',
                    'type_pax' => 'ADL'
                ]);

                $total = $total + ($quantity * $imports['tpl']['price']);
            }

            if($imports['tpl']['chd_pax'] > 0){
                
                $quantity = $imports['tpl']['chd_pax'];
                $unit_price = number_format($imports['tpl']['price'], 2, '.', '');                
                $amount = number_format($quantity * $unit_price, 2, '.', '')   ;   

                array_push($results,[
                    'description' => 'PRECIO NETO POR PERSONA-NIÑO - HAB TRIPLE',
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'amount' => $amount,
                    'type_room' => 'TPL',
                    'type_pax' => 'CHD'
                ]);

                $total = $total + ($amount);
            }

        }

        return [
            'details' => $results,
            'total' => $total,
            'file' => $file->id
        ];
    }
    
    public function getOtherDetails($total, $adults, $children=0): array
    {
        $price_pers = $total / ($adults+ $children);
        $details = [];
        

        if($adults>0){
            $amount = ($adults * $price_pers);
            array_push($details,[
                'description' => 'TARIFA NETA POR ADULTO',
                'quantity' => $adults,
                'unit_price' => $price_pers,
                'amount' => $amount
            ]);
        }

        if($children>0){
            $amount = ($children * $price_pers);
            array_push($details,[
                'description' => 'TARIFA NETA POR NIÑO',
                'quantity' => $children,
                'unit_price' => $price_pers,
                'amount' => $amount
            ]);
        }
        return $details;
    }


}
