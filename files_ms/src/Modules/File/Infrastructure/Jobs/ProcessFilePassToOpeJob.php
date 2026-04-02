<?php

namespace Src\Modules\File\Infrastructure\Jobs;
 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel; 
use App\Services\EventBridgeService;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassToOpeLogsModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassToOpeModel;
use Src\Modules\File\Presentation\Http\Traits\SqsNotification;

class ProcessFilePassToOpeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SqsNotification;
 
    private string $fileId; 

    /**
     * Create a new job instance.
     */
    public function __construct(string $fileId)
    { 
        $this->fileId = $fileId; 
    }

    /**
     * Execute the job.
     */
    public function handle(EventBridgeService $eventBridgeService)
    {
        try{
            // Log::info('entro al job');
            // file_put_contents("ope/pass_to_ope_".date('H_i_s').".txt", "ejecutado jobs");

            $sedes = $this->opeSedes();
 
            // $file = FileEloquentModel::query()->with([       
            //     'itineraries'=> function ($query) {
            //         $query->with('rooms.units.accommodations');
            //         $query->with('rooms.units.nights');  
            //         $query->where('status', 1); 
            //     },   
                
            //     'itineraries.services'=> function ($query) {
            //         $query->with([                       
            //             'compositions'=> function ($query) {
            //                 $query->with('units.accommodations'); 
            //                 $query->where('status', 1); 
            //                 // $query->where('is_programmable', 1);
            //             }                                      
            //         ]);
            //         $query->where('status', 1);   
            //     },            
            //     'itineraries.flights.accommodations',         
            //     'itineraries.accommodations',
            //     'passengers'
                
            // ])->where('id', $this->fileId)->where('status', 'OK')->first();
            
            $file = FileEloquentModel::query()->with([                 
                'itineraries'=> function ($query) {
                    $query->with([ 
                        'services',
                        'flights.accommodations'
                    ]);
                    // $query->with([                       
                    //     'services'=> function ($query) { 
                    //         $query->where('status', 1);  
                    //     },
                    //     'flights'=> function ($query) { 
                    //         $query->with('accommodations');
                    //         $query->where('status', 1);  
                    //     },                                    
                    // ]);                      
                    $query->where('status', 1);  
                } 
            ])->select('id','file_number', 'description','status', 'lang')->where('id', $this->fileId)->where('status', 'OK')->first();

            if($file){
          
                $flightIsInOpe  = $this->validate_flights($file);                    
                $hotelIsInOpe = $this->validate_hotel($file);
                $serviceIsInOpe = $this->validate_service($file, $flightIsInOpe);
            
                $itineraryProcess = array_merge($flightIsInOpe, $hotelIsInOpe, $serviceIsInOpe);                 
                $processedServiceSedes = $this->validate_pass_to_ope($sedes, $itineraryProcess);  
              
                if(count($processedServiceSedes)>0){
                                       
                    // Log::info('processedServiceSedes:', $processedServiceSedes);

                    // $is_in_open_passenger = $this->validate_passengers($file);
                    // if($is_in_open_passenger == true){ 
                        $send_pass_to_ope_complete = true; 
                        $date_reg = date('Y-m-d H:i:s'); 
                        foreach($processedServiceSedes as $sedes){
                            
                            if($sedes['sent_to_ope'] == true)
                            {
                                $processed_services = json_encode($sedes);

                                $this->sendEventOpe($eventBridgeService, $file->file_number, $sedes['sede'] , 'add');
                                
                                $file_pass_to_ope = FilePassToOpeModel::where('file_id', $file->id)->where('type', 'send')->where('sede', $sedes['sede'])->get();

                                if(count($file_pass_to_ope) == 0)
                                {
                                    $log = new FilePassToOpeModel();
                                    $log->file_id = $file->id;
                                    $log->type = 'send';
                                    $log->sede = $sedes['sede'];
                                    $log->processed_services = $processed_services;
                                    $log->created_at = $date_reg;
                                    $log->updated_at = $date_reg;                                
                                    $log->save();

                                    $itinerary_ids = [];
                                    foreach($sedes['servicesZones'] as $itinerary)
                                    {         
                                        if($itinerary['entity'] != 'hotel')
                                        {        
                                            array_push($itinerary_ids, $itinerary['id']);                              
                                        }
                                    }

                                    $this->send_notification_status([
                                        'file_number' => $file->file_number,
                                        "itinerary_ids" => $itinerary_ids,
                                        "action" => "sent-ope"
                                    ]); 

                                }else{
                                    $file_pass_to_ope = FilePassToOpeModel::where('file_id', $file->id)->where('type', 'send')->where('sede', $sedes['sede'])->first();
                                    $file_pass_to_ope->updated_at = $date_reg; 
                                    $file_pass_to_ope->save();
                                } 

                                $log = new FilePassToOpeLogsModel();
                                $log->file_id = $file->id;
                                $log->type = 'send';
                                $log->sede = $sedes['sede'];
                                $log->processed_services = $processed_services;
                                $log->created_at = $date_reg;
                                $log->updated_at = $date_reg;                            
                                $log->save();
                            
                                foreach($sedes['servicesZones'] as $itinerary)
                                {         
                                    if($itinerary['entity'] != 'hotel')
                                    {                                                           
                                        $fileItineraryModel = FileItineraryEloquentModel::find($itinerary['id']);                                    
                                        $fileItineraryModel->sent_to_ope=2;
                                        $fileItineraryModel->save();                               
                                    }
                                }
                            }else{

                                if(count($sedes['servicesZones'])>0) // solo si esto lleno es porque la zona tiene servicios y lo consideramos 
                                {
                                    $send_pass_to_ope_complete = false; 

                                    // $file_pass_to_ope = FilePassToOpeModel::where('file_id', $file->id)->where('type', 'send')->where('sede', $sedes['sede'])->first();
                                    // if($file_pass_to_ope)
                                    // {
                                    //     $file_pass_to_ope->delete();
                                    //     $this->sendEventOpe($eventBridgeService, $file->file_number, $sedes['sede'], 'delete');  

                                    //     $itinerary_ids = [];
                                    //     foreach($sedes['servicesZones'] as $itinerary)
                                    //     {         
                                    //         if($itinerary['entity'] != 'hotel')
                                    //         {                                                           
                                    //             $fileItineraryModel = FileItineraryEloquentModel::find($itinerary['id']);                                    
                                    //             $fileItineraryModel->sent_to_ope = $itinerary['is_in_ope'];
                                    //             $fileItineraryModel->save();  
                                    //             array_push($itinerary_ids, $itinerary['id']);                             
                                    //         }
                                    //     } 
                                        
                                    //     $this->send_notification_status([
                                    //         'file_number' => $file->file_number,
                                    //         "itinerary_ids" => $itinerary_ids,
                                    //         "action" => "cancel-sent-ope"
                                    //     ]); 
                                    // }

                                    
                                }                           
                            }
                        }
                
                        if($send_pass_to_ope_complete == true)
                        {
                            $file_pass_to_ope = FileEloquentModel::find($file->id);

                            if($file_pass_to_ope->revision_stages != 2)
                            {
                                $file_pass_to_ope->revision_stages=2;
                                $file_pass_to_ope->save();

                                $this->send_update_file([
                                    'file_id' => $file->id,
                                    'file_number' => $file->file_number,
                                    'description' => $file->description,
                                    'lang' => $file->lang,
                                    "revision_stages" => 2,
                                    "ope_assign_stages" => 0,
                                ]); 
                            }
                            
                        }

                    // }
                }
                // else{
                //     // si no encontramos servicios con send_to_ope elimnamos todos los destinos que se enviaron a ope
                //     FilePassToOpeModel::where('file_id', $file->id)->where('type', 'send')->delete();
                // }                                                                   
            } 

        } catch (\DomainException $domainException) {
             dd($domainException->getMessage());
        }
    }
    
    public function sendEventOpe(EventBridgeService $eventBridgeService, $file, $sede, $status){
        
        $detail = [
            'headquarter' => $sede,
            'file' => $file,
            'status' => $status,
        ];
        $source = 'a3.files';
        $detailType = 'file.to-ope';
        $eventBusName = 'default'; // o el nombre de tu bus de eventos

        $result = $eventBridgeService->putEvent($detail, $source, $detailType, $eventBusName);
    }

    public function validate_pass_to_ope($sedes, $itineraryProcess){ 
       
        $dataServiceByZones = [];
        // $fileItineraries = FileItineraryEloquentModel::where('file_id', $file->id)->get();
        foreach($sedes as $sede){

            if(!isset($dataServiceByZones[$sede->headquarter])){
                $dataServiceByZones[$sede->headquarter] = [
                    'sede' => $sede->headquarter,
                    'sent_to_ope' => false,
                    'emails' => $sede->emails,
                    'servicesZones' => []
                ];
            }

            foreach($sede->zones as $zone){

                foreach($itineraryProcess as $fileItinerary){
                    if($fileItinerary['city_in_iso'] == $zone){
                        // array_push($dataServiceByZones[$sede->headquarter]['servicesZones'], $fileItinerary);
                        array_push($dataServiceByZones[$sede->headquarter]['servicesZones'], $this->itineraryField((object)$fileItinerary, NULL));
                    }else{
                                               
                        if(substr($fileItinerary['object_code'],0,3) == 'AEI' and $fileItinerary['country_in_iso'] != 'PE' and $fileItinerary['city_out_iso'] == $zone){
                            array_push($dataServiceByZones[$sede->headquarter]['servicesZones'], $this->itineraryField((object)$fileItinerary, NULL));
                        }
                        
                    }
                }
            }                         
        }
   
        $nopasaron = [];
        $dataServiceByZoneToOpe = [];
        foreach($dataServiceByZones as $sede => $dataServiceByZone){
            $paseToOpe = true;
            if(count($dataServiceByZone['servicesZones'])>0){
                foreach($dataServiceByZone['servicesZones'] as $services){
                    if(!$services['is_in_ope'] and $services['entity'] != 'hotel'){ // no considera los hoteles que esten validados para pasar a OPE
                        $paseToOpe = false;
                        // Log::info('service error', $services);
                    }
                }
            }else{
                $paseToOpe = false;
                array_push($nopasaron,$dataServiceByZone );
            }            
            $dataServiceByZone['sent_to_ope'] = $paseToOpe;                             
            array_push($dataServiceByZoneToOpe, $dataServiceByZone);
        }
 
        return $dataServiceByZoneToOpe;

    }

    public function validate_service($file, $fileItinerarieFlights){
 
        $serviceIsInOpe = [];
        foreach($file->itineraries as $itinerary){
                 
            if(in_array($itinerary->entity, ['service', 'service-temporary'])){ //and !$itinerary->is_in_ope
                
               $is_in_open = true;    
               
               if($itinerary->hotel_origin or $itinerary->hotel_destination)
               {      
                    if(($itinerary->start_time  == null or $itinerary->start_time == '00:00:00') or ($itinerary->departure_time  == null or $itinerary->departure_time == '00:00:00'))
                    {
                        $is_in_open = false;
                        $itineraryFields = $this->itineraryField($itinerary, false);
                        $itineraryFields['services'] = $itinerary->services->toArray();
                        array_push($serviceIsInOpe, $itineraryFields);                        
                    }
               } 
               
               if($is_in_open == true)
               {
                    foreach($itinerary->services as $service){ 
                            

                            $is_in_open_service_prograbale = true; 
                            if($service->start_time == null or $service->departure_time == null){
                        
                                $is_in_open = false;
                                $is_in_open_service_prograbale = false;
                                // throw new \DomainException("schedules not entered");
                            }            
                
                            if($service->start_time == '00:00:00' and $service->departure_time == '00:00:00'){
                                $is_in_open = false;
                                $is_in_open_service_prograbale = false;
                                // throw new \DomainException("schedules not entered");
                            } 

                            // if($service->start_time == $service->departure_time){
                            //     $is_in_open = false;
                            //     $is_in_open_service_prograbale = false;
                            //     // throw new \DomainException("schedules not entered");
                            // }
                                                
                            if($service->sent_to_ope != $is_in_open_service_prograbale){
                                $service->sent_to_ope=$is_in_open;
                                $service->save();  
                            }                


                            // if(count($service->compositions)>0) // quiere decir que es un servicio que tiene entre sus componentes un servicio programable
                            // {
                            //     $is_in_open_service_prograbale = true; 
                            //     if($service->start_time == null or $service->departure_time == null){
                            //         $is_in_open = false;
                            //         $is_in_open_service_prograbale = false;
                            //         // throw new \DomainException("schedules not entered");
                            //     }            
                    
                            //     if($service->start_time == '00:00:00' or $service->departure_time == '00:00:00'){
                            //         $is_in_open = false;
                            //         $is_in_open_service_prograbale = false;
                            //         // throw new \DomainException("schedules not entered");
                            //     } 

                            //     if($service->start_time == $service->departure_time){
                            //         $is_in_open = false;
                            //         $is_in_open_service_prograbale = false;
                            //         // throw new \DomainException("schedules not entered");
                            //     }
                                                    
                            //     if($service->sent_to_ope != $is_in_open_service_prograbale){
                            //         $service->sent_to_ope=$is_in_open;
                            //         $service->save();  
                            //     }
                            // }else{
                            //     // no importa si cumple con todos los requisitos
                            //     $is_in_open_service_no_programable = true;
                            //     if($service->start_time == null or $service->departure_time == null){ 
                            //         $is_in_open_service_no_programable = false;
                            //         // throw new \DomainException("schedules not entered");
                            //     }            
                    
                            //     if($service->start_time == '00:00:00' or $service->departure_time == '00:00:00'){
                                
                            //         $is_in_open_service_no_programable = false;
                            //         // throw new \DomainException("schedules not entered");
                            //     } 

                            //     if($service->start_time == $service->departure_time){
                                
                            //         $is_in_open_service_no_programable = false;
                            //         // throw new \DomainException("schedules not entered");
                            //     }
                                                    
                            //     $service->sent_to_ope=$is_in_open_service_no_programable;
                            //     $service->save();
                            // }  



                    } 
                        
                    if($is_in_open == true and count($itinerary->accommodations)>0){ // tiene que tener las acomodaciones completas.
        
                            // $is_in_open = $this->validateInOuntAirport($fileItinerarieFlights, $itinerary);
                            $is_in_open = true;
                            if($is_in_open == true){
                                $itineraryFields = $this->itineraryField($itinerary, true); 
                            }else{ 
                                $itineraryFields = $this->itineraryField($itinerary, false);
                            }    
                            $itineraryFields['services'] = $itinerary->services->toArray();
                            array_push($serviceIsInOpe, $itineraryFields);
                    }else{
                            $itineraryFields = $this->itineraryField($itinerary, false);
                            $itineraryFields['services'] = $itinerary->services->toArray();
                            array_push($serviceIsInOpe, $itineraryFields);
                    }
               }

               if($itinerary->sent_to_ope != $is_in_open){
                  $itinerary->sent_to_ope=$is_in_open;
                  $itinerary->save();  
               }                               

            }
              
        }

        return $serviceIsInOpe;
    }

    public function validateInOuntAirport($fileItinerarieFlights, $itinerary){

        $is_in_open = false;     

        $city_in_iso = $itinerary->city_in_iso;
        $zone_in_airport = $itinerary->zone_in_airport;

        $city_out_iso = $itinerary->city_out_iso;               
        $zone_out_airport = $itinerary->zone_out_airport;

        $date_in = $itinerary->date_in;

        $searchInOk = false;   $searchInOkArray=[] ;                            
        foreach($fileItinerarieFlights as $file_itinerary_flights){
            //  if($file_itinerary->city_in_iso == $city_in_iso and $file_itinerary->date_in == $date_in and $file_itinerary->is_in_ope == true){
             if($file_itinerary_flights['city_out_iso'] == $city_in_iso and $file_itinerary_flights['date_in'] == $date_in and $file_itinerary_flights['is_in_ope'] == true){
                 $searchInOk = true;
             }
            //  array_push($searchInOkArray, "$file_itinerary_flights->city_out_iso == $city_in_iso and $file_itinerary_flights->date_in == $date_in and $file_itinerary_flights->is_in_ope == true");
        }                   

        $searchOutOk = false;                     
        foreach($fileItinerarieFlights as $file_itinerary_flights){
            // if($file_itinerary->city_out_is == $city_out_is and $file_itinerary->date_in == $date_in and $file_itinerary->is_in_ope == true){
            if($file_itinerary_flights['city_in_iso'] == $city_out_iso and $file_itinerary_flights['date_in'] == $date_in and $file_itinerary_flights['is_in_ope'] == true){
                $searchOutOk = true;
            }
        }
            
        if($zone_in_airport and !$zone_out_airport){
            // dd($searchInOkArray);
            if($searchInOk){
                $is_in_open = true;
            }
        }
        if(!$zone_in_airport and $zone_out_airport){
            if($searchOutOk){
                $is_in_open = true;
            }
        }  
        
        if($zone_in_airport and $zone_out_airport){
            if($searchInOk and $searchOutOk){
                $is_in_open = true;
            }
        } 

        if(!$zone_in_airport and !$zone_out_airport){
            $is_in_open = true;
        }

        return $is_in_open;
    }

    public function validate_hotel($file){
        $hotelIsInOpe = [];
        foreach($file->itineraries as $itinerary){
            
            if($itinerary->entity == 'hotel'){  //and !$itinerary->is_in_ope
                
               $is_in_open = true;
            //    foreach($itinerary->rooms as $room){
            //         foreach($room->units as $unit){
            //             if($unit->confirmation_status != 1){
            //                 $is_in_open = false;
            //                 // throw new \DomainException("Unconfirmed hotels ".$itinerary->object_code);
            //             }
            //             if(count($unit->accommodations) == 0){
            //                 $is_in_open = false; 
            //                 // throw new \DomainException("non-associated passenger assignment hotel ".$itinerary->object_code.'-'.$unit->id);
            //             }                                                
            //         }
            //    } 
               
               if($is_in_open == true){ 
                    array_push($hotelIsInOpe, $this->itineraryField($itinerary, true));
               }else{
                    array_push($hotelIsInOpe, $this->itineraryField($itinerary, false));
               }

               if($itinerary->sent_to_ope != $is_in_open){
                    $itinerary->sent_to_ope=$is_in_open;
                    $itinerary->save();  
               }               

            }        
        }

        return $hotelIsInOpe;
       
    }    

    public function validate_flight_data($flight){
        if(!$flight->airline_name or !$flight->airline_code or !$flight->airline_number or !$flight->departure_time or !$flight->arrival_time or !$flight->nro_pax){
            return false;
        }
        return true;        
    }

    public function validate_flights($file){
        $flightIsInOpe = [];
        foreach($file->itineraries as $itinerary){            
            if($itinerary->entity == 'flight'){  // and !$itinerary->is_in_ope
                            
                $is_in_open = true;    

                // if($itinerary->object_code == "AECFLT"){ //vuelo nacional

                // if($itinerary->city_in_iso == '' or $itinerary->city_out_iso == ''){  
                //     $is_in_open = false; 
                // }

                // if(!isset($itinerary->flights) or !is_array($itinerary->flights) or count($itinerary->flights) == 0)
                // {
                //     $is_in_open = false;  
                // }


                $total_passenger = $itinerary->total_adults + $itinerary->total_children + $itinerary->total_infants ;
                $total_asign = 0;

                foreach($itinerary->flights as $flight){ 
                    if(!$this->validate_flight_data($flight)){
                        $is_in_open = false;  
                    }else{
                        if(count($flight->accommodations) == 0){
                            $is_in_open = false; 
                            // throw new \DomainException("non-associated passenger assignment flights");
                        }else{
                            $total_asign = $total_asign + count($flight->accommodations);                                
                        }  
                    }
                                            
                } 
        
                if($total_passenger != $total_asign){
                    $is_in_open = false; 
                }

                // }


                if($is_in_open == true){            
                    array_push($flightIsInOpe, $this->itineraryField($itinerary, true));
                }else{ 
                    array_push($flightIsInOpe, $this->itineraryField($itinerary, false));
                }     

                if($itinerary->sent_to_ope != $is_in_open){
                    $itinerary->sent_to_ope=$is_in_open;
                    $itinerary->save();  
                }
                                                        
            }            
        }

        return $flightIsInOpe;
    }

    public function itineraryField($itinerary, $is_in_ope){
        $data = [
            'id' => $itinerary->id,
            'entity' => $itinerary->entity,
            'object_code' => $itinerary->object_code,
            'name' => $itinerary->name,
            'category' => $itinerary->category,
            'date_in'=> $itinerary->date_in,
            'date_out'=> $itinerary->date_out,
            'zone_in_airport' => $itinerary->zone_in_airport,
            'zone_out_airport' => $itinerary->zone_out_airport,
            'city_in_iso' => $itinerary->city_in_iso,
            'city_out_iso' => $itinerary->city_out_iso,
            'country_in_iso' => $itinerary->country_in_iso, 
            'country_out_iso' => $itinerary->country_out_iso
        ];

        if($is_in_ope === NULL ){
            $data['is_in_ope'] = $itinerary->is_in_ope;            
        }else{
            $data['is_in_ope'] = $is_in_ope;
        }
        $data['services'] = count($itinerary->services)>0 ? $itinerary->services : [];
        return  $data;

    }

    public function validate_passengers($file){
        $is_in_open = true; 
        foreach($file->passengers as $passenger){
            if(!$passenger->name && !$passenger->surnames && !$passenger->doctype_iso && !$passenger->document_number && !$passenger->suggested_room_type && !$passenger->genre && !$passenger->country_iso && !$passenger->phone && !$passenger->document_url){
                $is_in_open = false;
                // throw new \DomainException("Incomplete passenger information");
            }
        }

        if($file->passenger_changes == true){
            $is_in_open = false;
            // throw new \DomainException("modified accommodation");
        }
        return $is_in_open;
    }

    public function opeSedes(){


        $json = '{
            "data":[
                {
                    "process":"file-to-ope",
                    "data":[
                            {
                            "headquarter":"LIM",
                            "zones":[
                                "LIM",
                                "LIN",
                                "PCO",
                                "PER"
                            ],
                            "emails":{
                                "to":[
                                    "programacion@limatours.com.pe"
                                ],
                                "cc":[
                                    
                                ],
                                "bcc":[
                                    
                                ]
                            }
                            },
                            {
                            "headquarter":"CUS",
                            "zones":[
                                "CUS",
                                "CUZ",
                                "MPI",
                                "PY1",
                                "UR1",
                                "URT",
                                "URU"
                            ],
                            "emails":{
                                "to":[
                                    "programacus@limatours.com.pe"
                                ],
                                "cc":[
                                    
                                ],
                                "bcc":[
                                    
                                ]
                            }
                            },
                            {
                            "headquarter":"AQP",
                            "zones":[
                                "AQP",
                                "AQV",
                                "COL"
                            ],
                            "emails":{
                                "to":[
                                    "reportesaqp@limatours.com.pe"
                                ],
                                "cc":[
                                    
                                ],
                                "bcc":[
                                    
                                ]
                            }
                            },
                            {
                            "headquarter":"PUN",
                            "zones":[
                                "PUV",
                                "PUN"
                            ],
                            "emails":{
                                "to":[
                                    "reportespuno@limatours.com.pe"
                                ],
                                "cc":[
                                    
                                ],
                                "bcc":[
                                    
                                ]
                            }
                            }
                        ]
                    }
                ]
        }';

        $json = json_decode($json);
        $sedes = $json->data[0]->data;
        return $sedes;
        // $serviceOpe = new OpeExternalApiService();            
        // $sedes = $serviceOpe->searchZones();
        // $sedes = $sedes[0]->data;
        // return $sedes;
    }
     
 
}
