<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;
use Carbon\Carbon;

final class FileItineraryServiceByCommunication extends ValueObjectArray
{
    public readonly array $file;
    public readonly string $force_send_communication;

    public function __construct(array $file , array $new = [], array $delete = [], array $update = [], $itinerary_id = null, $force_send_communication = false)
    {
        parent::__construct($file);    
        $this->force_send_communication = $force_send_communication;  

        $updates = [];
        if($itinerary_id !== null){
            $updates = $this->update_itinerary($file, $update, $itinerary_id);
        }else{
            $updates = $this->updates($file, $update);  
        }
 
        $results = [
            'news' => $this->news($file, $new),
            'deletes' => $this->deletes($file, $delete),
            'updates' => $updates 
            
        ];
        $this->file = $results;
    }

    /**
     * @return array
     */
    public function news($file ,$news): array
    {
        // return [];
        $proveedors = [];
        foreach($news as $service)
        {   $total = 0;
            foreach($service['components'] as $component)
            {                
                if( isset($component['supplier']['code_request_book'])  and ( $component['supplier']['send_communication'] == 'S' or $this->force_send_communication == true) ){
                   
                    if(!isset($proveedors[$component['supplier']['code_request_book']])){
                        $proveedors[$component['supplier']['code_request_book']] = [
                            'code_request_book' => $component['supplier']['code_request_book'],
                            'supplier_name' => '',
                            'supplier_emails' => '',
                            'send_communication' => $component['supplier']['send_communication'],
                            'html' => '',
                            'components' => [],
                            'total_amount_cost' => 0,
                            'notas' => '',
                            'attachments' => []                        
                        ];
                    }

                    array_push($proveedors[$component['supplier']['code_request_book']]['components'], [
                        'date_in' => $this->isFormatDB($component['date_in']) ? $component['date_in'] : Carbon::createFromFormat('d/m/Y', $component['date_in'])->format('Y-m-d'),
                        'name' => $component['name'],
                        'amount_cost' => $component['amount_cost'] 
                    ]);
                     
                    $proveedors[$component['supplier']['code_request_book']]['total_amount_cost'] = $proveedors[$component['supplier']['code_request_book']]['total_amount_cost'] + $component['amount_cost'];                    

                }
                
            }
            
        }
        
        return array_values($proveedors);          
    }

    /**
     * @return array
     */
    public function deletes($file ,$deletes): array
    {
        // return [];
        $compositions = [];
        foreach($file['itineraries'] as $itinerary){

            if($itinerary["entity"] == "service"){

                foreach($itinerary["services"] as $service){

                    if(in_array($service["id"], $deletes)){
                        foreach( $service['compositions'] as $composition){
                            $composition['supplier'] = $composition['supplier'];
                            unset($composition['units']); 
                            array_push($compositions, $composition);
                        }
                    }
                }
                
            }
        }

        // dd($compositions,$deletes);

        $proveedors = [];
        
        foreach($compositions as $component)
        {     
            if(isset($component['supplier']['code_request_book'])  and ( $component['supplier']['send_communication'] == 'S' or $this->force_send_communication == true) ){

                if(!isset($proveedors[$component['supplier']['code_request_book']]) ){  
                    $proveedors[$component['supplier']['code_request_book']] = [
                        'code_request_book' => $component['supplier']['code_request_book'],
                        'supplier_name' => '',
                        'supplier_emails' => '',
                        'send_communication' => $component['supplier']['send_communication'],
                        'html' => '',
                        'components' => [],
                        'total_penality' => 0,
                        'notas' => '',
                        'attachments' => []                         
                    ];
                }

                array_push($proveedors[$component['supplier']['code_request_book']]['components'], [
                    'date_in' => $this->isFormatDB($component['date_in']) ? $component['date_in'] : Carbon::createFromFormat('d/m/Y', $component['date_in'])->format('Y-m-d'),
                    'name' => $component['name'],
                    'penality' => $component['penalty']['penality_cost'],
                    'penality_detail' => $component['penalty']['message']
                ]);  
    
                $proveedors[$component['supplier']['code_request_book']]['total_penality'] = $proveedors[$component['supplier']['code_request_book']]['total_penality'] + $component['penalty']['penality_cost'];
            }            

        }

        return array_values($proveedors);
    }    

    /**
     * @return array
     */
    public function updates($file ,$updates): array
    {
        $news = [];
        $deletes = [];
        foreach($updates as $update){
            array_push($news, $update['service_change']);
            array_push($deletes, $update['service_id']);
        }
      
        $news = $this->news($file ,$news);
        $deletes = $this->deletes($file ,$deletes);
 
        $results = $this->update_data($deletes, $news);

        return $results;
    }   

 
        /**
     * @return array
     */
    public function update_itinerary($file ,$news, $itinerary_id): array
    {
      
        $deletes = [];
        foreach($file['itineraries'] as $itinerary){
            if($itinerary["id"] == $itinerary_id){
                foreach($itinerary["services"] as $service){
                    array_push($deletes, $service['id']);                                         
                }                
            }
        }
    
        $news = $this->news($file ,$news);
        $deletes = $this->deletes($file ,$deletes);
      
        $results = $this->update_data($deletes, $news);
        
        return $results;
    }   


    public function update_data($deletes, $news){

        $results = [];

        if(count($deletes)>0 and count($news)>0){

            foreach($deletes as $delete){
                $providers_news = [];
                foreach($news as $new){
                
                    if($delete['code_request_book'] == $new['code_request_book']){
                        $providers_news = $new;
                        break;
                    }
                }

                if(count($providers_news)>0){

                    array_push($results, [
                        'code_request_book' => $delete['code_request_book'],
                        'supplier_name' => $delete['supplier_name'],
                        'supplier_emails' => $delete['supplier_emails'],
                        'send_communication' => $delete['send_communication'],
                        'code_request_book' => $delete['code_request_book'],
                        'html' => '',
                        'cancellation' => $delete,
                        'reservations' => $providers_news,
                        'notas' => '',
                        'attachments' => []                         
                    ]);
                }

            }

            $deleteFree = [];
            $newsFree = [];
            foreach($deletes as $delete){
                $deleteFreeBase = [];
                foreach($results as $result){                                          
                    if($delete['code_request_book'] == $result['cancellation']['code_request_book']){
                        $deleteFreeBase = $delete;
                    }                     
                }

                if(count($deleteFreeBase) == 0){                 
                    array_push($deleteFree, $delete);
                }

            }

            foreach($news as $new){
                $newsFreeBase = [];
                foreach($results as $result){ 
                    if($new['code_request_book'] == $result['reservations']['code_request_book']){
                        $newsFreeBase = $new;
                    } 
                }

                if(count($newsFreeBase) == 0){
                    array_push($newsFree, $new);
                }

            }     
            
            array_push($results, [
                'cancellation' => $deleteFree,
                'reservations' => $newsFree,
            ]);
        
        }else{
      
            if(count($deletes)>0){

                array_push($results, [
                    'cancellation' => $deletes,
                    'reservations' => [] 
                ]);                
            }
    
            if(count($news)>0){
                array_push($results, [
                    'cancellation' => [],
                    'reservations' => $news 
                ]);                 
            }
        }

        return $results;

    }

    public function isFormatDB($fecha)
    {
        $formatoEsperado = 'Y-m-d';

        try {
            $fechaCarbon = Carbon::createFromFormat($formatoEsperado, $fecha); 
            return $fechaCarbon && $fechaCarbon->format($formatoEsperado) === $fecha;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->file;
    }
}
