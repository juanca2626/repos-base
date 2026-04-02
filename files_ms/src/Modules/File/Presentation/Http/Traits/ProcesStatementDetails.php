<?php

namespace Src\Modules\File\Presentation\Http\Traits;

trait ProcesStatementDetails
{
  
    public function update_statement_detail($file): array|null
    {

        $accommodations = [];

        if($file->suggested_accommodation_sgl > 0 or $file->suggested_accommodation_dbl > 0  or $file->suggested_accommodation_tpl > 0 ){
            $accommodations = [
                'sgl' => $file->suggested_accommodation_sgl,
                'dbl' => $file->suggested_accommodation_dbl,
                'tpl' => $file->suggested_accommodation_tpl,
            ];
        }

        $itineraries = [];
        $total_amount=0; 
        foreach($file->itineraries as $itinerary){
            if($itinerary->add_to_statement !== 1){
                array_push($itineraries, $itinerary);
                $total_amount = $total_amount + $itinerary->total_amount;
            }
        }
 
        $file->itineraries = $itineraries;
        $file->total_amount = $total_amount;

        $details = [];        
        if(count($itineraries)>0){

            if(count($accommodations) == 0)
            {
                $details = $this->getOtherDetails($file->total_amount, $file->adults, $file->children);
            }else{                
                $details = $this->detailOccupations($file, $accommodations);
                
            }
        }
        
        return $this->getDetailChanges($file, $details);
    }
    
    public function getDetailChanges($file, $details): array|null
    {
        
        if(count($details) == 0)
        {
            return null;
        }

        $statement_details = $file->statement->details->makeHidden(['created_at', 'updated_at', 'deleted_at']);     
        $statementDetails = $statement_details->toArray(); 
        $error_validate_pax = false;

        foreach($details as $detail)
        {
            $find = false;
            foreach($statementDetails as $index => $st_detail)
            {
              
                if(($detail['type_room'] == $st_detail['type_room']) and ($detail['type_pax'] == $st_detail['type_pax'])){    
                   $quantity = $st_detail['quantity'];               
                   $quantity_new = $detail['quantity'];
                   $unit_price = number_format(($detail['amount'] + $st_detail['amount']) /  $detail['quantity'], 2, '.', '');
                   $amount = number_format($quantity * $unit_price, 2, '.', '');     
                   $statementDetails[$index]['unit_price'] = $unit_price;
                   $statementDetails[$index]['amount'] = $amount;
                   $statementDetails[$index]['before'] =  $st_detail['amount'];
                   $statementDetails[$index]['after'] =  $amount;
                   $statementDetails[$index]['type_room'] =  $st_detail['type_room'];
                   $statementDetails[$index]['type_pax'] =  $st_detail['type_pax'];
                   $find = true;
                    
                   if($quantity != $quantity_new )
                   {
                      $error_validate_pax = true;
                   }
               }
                
            }
            
            if($find == false){
                $error_validate_pax = true;
                array_push($statementDetails, [
                    'id' => NULL,
                    'file_statement_id' => $file->statement->id,
                    'description' => $detail['description'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'amount' => $detail['amount'],
                    'before' => 0,
                    'after' => $detail['amount'],
                    'type_room' => $detail['type_room'],
                    'type_pax' => $detail['type_pax'] 
                ]);
            }
        }

        $before_total = 0;
        $after_total = 0;

        if($error_validate_pax == true)
        {
         
            $details = $this->getOtherDetails($file->total_amount, $file->adults, $file->children);         
            $table = [];
            $statementDetails = $statement_details->toArray();
            foreach($statementDetails as $st_detail)
            {                     
                array_push($table, [
                    'before' => $st_detail['amount'],
                    'after' => 0,
                    'room' => isset($st_detail['type_room']) ? $st_detail['type_room'] : '-',
                    'type_pax' => isset($st_detail['type_pax']) ? $st_detail['type_pax'] : '-'
                ]);
                $before_total = $before_total + $st_detail['amount'];
            }

            foreach($details as $deta)
            {
                $after_total = $after_total + $deta['amount'];

                array_push($table, [
                    'before' => 0,
                    'after' =>  $deta['amount'],
                    'room' => $deta['description'],
                    'type_pax' => '-'
                ]); 
                
                array_push($statementDetails, [
                    'id' => NULL,
                    'file_statement_id' => $file->statement->id,
                    'description' => $deta['description'],
                    'quantity' => $deta['quantity'],
                    'unit_price' => $deta['unit_price'],
                    'amount' => $deta['amount'],                    
                    'type_room' => $deta['type_room'],
                    'type_pax' => $deta['type_pax'] 
                ]);                
            }

            $after_total = $before_total + $after_total;
             
        }else{
            $table = [];
            foreach($statementDetails as $statementDetail)
            {
                $before = isset($statementDetail['before']) ? $statementDetail['before'] : 0;
                $after = isset($statementDetail['after']) ? $statementDetail['after'] : 0;
    
                if($statementDetail['type_room'] == null and $statementDetail['type_pax'] == null){
                  
                    if($statementDetail['id'] !== NULL){
                    
                        $before = $statementDetail['amount'];
                        $after = 0;
                    }else{
                        $before = 0;
                        $after = $statementDetail['amount'];
                    }
                }
    
                array_push($table, [
                    'before' => $before,
                    'after' => $after,
                    'room' => isset($statementDetail['type_room']) ? $statementDetail['type_room'] : '-',
                    'type_pax' => isset($statementDetail['type_pax']) ? $statementDetail['type_pax'] : '-'
                ]);

                $before_total = $before_total + $before; 
                $after_total = $after_total + $after;

            }

          
        }
 
        $itinerary_update = [];
        foreach($file->itineraries as $itinerary)
        {
            array_push($itinerary_update , $itinerary->id);        
        
        };

        return [
            'table_changes' => [
                'table' => $table,
                'before' => $before_total,
                'after' => number_format($after_total, 2, '.', ''),
                'mkp' => $file->markup_client,
            ],
            'statement_id' => $file->statement->id,
            'update_statement' => $statementDetails,
            'update_itineraries' => $itinerary_update,
            'deadline' => $file->statement->deadline
        ];
    }

    public function getDetailNew($file, $details): array|null
    {
 
        if(count($details) == 0)
        {
            return null;
        }


        $statementDetails = [];
        foreach($details as $detail)
        {                    
            array_push($statementDetails, [
                'id' => NULL,
                'file_statement_id' => NULL,
                'description' => $detail['description'],
                'quantity' => $detail['quantity'],
                'unit_price' => $detail['unit_price'],
                'amount' => $detail['amount'],
                'before' => 0,
                'after' => $detail['amount'],
                'type_room' => $detail['type_room'],
                'type_pax' => $detail['type_pax'] 
            ]);            
        }

        $before_total = 0;
        $after_total = 0;
        
        $table = [];
        foreach($statementDetails as $statementDetail)
        {
            $before = isset($statementDetail['before']) ? $statementDetail['before'] : 0;
            $after = isset($statementDetail['after']) ? $statementDetail['after'] : 0;

            if($statementDetail['type_room'] == null and $statementDetail['type_pax'] == null){
                
                if($statementDetail['id'] !== NULL){
                
                    $before = $statementDetail['amount'];
                    $after = 0;
                }else{
                    $before = 0;
                    $after = $statementDetail['amount'];
                }
            }

            array_push($table, [
                'before' => $before,
                'after' => $after,
                'room' => isset($statementDetail['type_room']) ? $statementDetail['type_room'] : '-',
                'type_pax' => isset($statementDetail['type_pax']) ? $statementDetail['type_pax'] : '-'
            ]);

            $before_total = $before_total + $before; 
            $after_total = $after_total + $after;

        }
    
        $itinerary_update = [];
        foreach($file->itineraries as $itinerary)
        {
            array_push($itinerary_update , $itinerary->id);        
        
        };

        $deadline = NULL;
        $time = strtotime($file->date_in);             
        if(!$file->client_have_credit)
        {
            $deadline = date('Y-m-d', strtotime('-10 days', $time));
        }

        return [
            'table_changes' => [
                'table' => $table,
                'before' => $before_total,
                'after' => number_format($after_total, 2, '.', ''),
                'mkp' => $file->markup_client,
            ],
            'statement_id' => NULL,
            'update_statement' => $statementDetails,
            'update_itineraries' => $itinerary_update,
            'deadline' => $deadline
        ];


    }

    public function create_statement_detail($file): array
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

    public function create_statement_detail_new($file): array|null
    {

        $accommodations = [];

        if($file->suggested_accommodation_sgl > 0 or $file->suggested_accommodation_dbl > 0  or $file->suggested_accommodation_tpl > 0 ){
            $accommodations = [
                'sgl' => $file->suggested_accommodation_sgl,
                'dbl' => $file->suggested_accommodation_dbl,
                'tpl' => $file->suggested_accommodation_tpl,
            ];
        }

        $itineraries = [];
        $total_amount=0; 
        foreach($file->itineraries as $itinerary){
            if($itinerary->add_to_statement !== 1){
                array_push($itineraries, $itinerary);
                $total_amount = $total_amount + $itinerary->total_amount;
            }
        }
 
        $file->itineraries = $itineraries;
        $file->total_amount = $total_amount;

        $details = [];        
        if(count($itineraries)>0)
        {                                  
            if(count($accommodations) == 0)
            {
                $details = $this->getOtherDetails($file->total_amount, $file->adults, $file->children);
            }else{
                
                $details = $this->detailOccupations($file, $accommodations);            
            }            
        }

        return $this->getDetailNew($file, $details);
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
        $validate_assign_pax = true;
        
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

        // dd([
        //     'details' => $results,
        //     'total' => $total,
        //     'file' => $file->id
        // ]);
        return $results;
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
                'id' => NULL,
                'description' => 'TARIFA NETA POR ADULTO',
                'quantity' => $adults,
                'unit_price' => $price_pers,
                'amount' => $amount,
                'type_room' => '',
                'type_pax' => ''
            ]);
        }

        if($children>0){
            $amount = ($children * $price_pers);
            array_push($details,[
                'id' => NULL,
                'description' => 'TARIFA NETA POR NIÑO',
                'quantity' => $children,
                'unit_price' => $price_pers,
                'amount' => $amount,
                'type_room' => '',
                'type_pax' => ''
            ]);
        }
        return $details;
    }


}
