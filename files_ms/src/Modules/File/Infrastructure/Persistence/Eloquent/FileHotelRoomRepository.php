<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
use Carbon\Carbon;
use Src\Modules\File\Domain\Repositories\FileHotelRoomRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomEloquentModel;  
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileAmountReasonEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryRoomAmountLogEloquentModel; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileRoomAmountLogEloquentModel; 

class FileHotelRoomRepository implements FileHotelRoomRepositoryInterface
{

    public function updateAmountCost(int $id, array $params): array
    {  
 
        $fileHotelRoomEloquent = FileHotelRoomEloquentModel::query()->with([
            'fileItinerary.file',
            'fileRoomAmountLogs', 
            'fileItinerary',
            'units.nights'
        ])->find($id);
            
        // Preoceso, crear registro en la tabla file_room_amount_logs, en esta tabla siempre habra 1 registro y si se crea uno nuevo se elimina el que estaba activo pero necesitamos recuperar el monto que tenia
        // actualmente el servicio actualizado

        $hotelRoomAmountLog = $fileHotelRoomEloquent->fileRoomAmountLogs->whereNull('deleted_at')->first();
    
        $fileRoomAmountLogEloquentModel = FileRoomAmountLogEloquentModel::create([
            'file_amount_type_flag_id' => $params['file_amount_type_flag_id']->value(), 
            'file_amount_reason_id' => $params['file_amount_reason_id']->value(), 
            'file_hotel_room_id' => $id, 
            'user_id' => $params['user_id']->value(),
            'amount_previous' => $hotelRoomAmountLog->amount, 
            'amount' => $params['amount_cost']->value(), 
        ]);

        $hotelRoomAmountLog->delete();
        $influences_sale = false;
        // Si la razon de la actualizacion de monto influye a la venta tenemos que generar un registro en la tabla file_itinerary_service_amount_logs  y actualizar el campos total_amount(venta) del itinerario        
        $amountReason = FileAmountReasonEloquentModel::find($params['file_amount_reason_id']->value()); 
        if($amountReason->influences_sale == 1){
            $influences_sale = true;
            $newServiceAmountCost = $params['amount_cost']->value();  // 120     80
            $serviceAmountCost = $fileHotelRoomEloquent->amount_cost;   // 100    100
            $value = 0;

            if($serviceAmountCost < $newServiceAmountCost){  // le aumentamos 20 al precio de venta
               $diference = $newServiceAmountCost - $serviceAmountCost;
               $fileHotelRoomEloquent->fileItinerary->total_amount =  $fileHotelRoomEloquent->fileItinerary->total_amount + $diference;
               $value = $diference;
            }elseif($serviceAmountCost > $newServiceAmountCost){ // le restamos 20 al precio de venta
               $diference = $serviceAmountCost - $newServiceAmountCost;
               $fileHotelRoomEloquent->fileItinerary->total_amount =  $fileHotelRoomEloquent->fileItinerary->total_amount - $diference;
               $value = $diference * -1;
            }
            
            $fileHotelRoomEloquent->fileItinerary->save();
            
            FileItineraryRoomAmountLogEloquentModel::create([
                'file_itinerary_id' => $fileHotelRoomEloquent->fileItinerary->id, 
                'file_room_amount_log_id' => $fileRoomAmountLogEloquentModel->id, 
                'value' => $value,
                'markup' => $fileHotelRoomEloquent->fileItinerary->profitability
            ]);
        }else{
            FileItineraryRoomAmountLogEloquentModel::create([
                'file_itinerary_id' => $fileHotelRoomEloquent->fileItinerary->id, 
                'file_room_amount_log_id' => $fileRoomAmountLogEloquentModel->id, 
                'value' => $params['amount_cost']->value(),
                'markup' => $fileHotelRoomEloquent->fileItinerary->profitability
            ]);
        } 

        // actualizamos los candados de todos los units y night
        foreach($fileHotelRoomEloquent->units as $unit){
            foreach($unit->nights as $night){
                $night->file_amount_type_flag_id =  $params['file_amount_type_flag_id']->value(); 
                $night->save();
            }
            $unit->file_amount_type_flag_id =  $params['file_amount_type_flag_id']->value(); 
            $unit->save();            
        }

        // actualizamos el nuevo monto 
        $fileHotelRoomEloquent->file_amount_type_flag_id =  $params['file_amount_type_flag_id']->value();
        $fileHotelRoomEloquent->amount_cost = $params['amount_cost']->value();
        $fileHotelRoomEloquent->save();
        
        return [
            "stela" => [
                "code" =>  $fileHotelRoomEloquent->fileItinerary->object_code,
                "date_in" =>  Carbon::parse($fileHotelRoomEloquent->fileItinerary->date_in)->format('d/m/Y'),
                "start_time" => substr($fileHotelRoomEloquent->fileItinerary->start_time, 0, 5),
                "auto_order" =>  1,
                "amount_cost" => number_format($fileHotelRoomEloquent->amount_cost, 2, '.', ''),
                "amount_sale" =>  number_format($fileHotelRoomEloquent->amount_cost * (1 + ($fileHotelRoomEloquent->fileItinerary->markup_created) / 100), 2, '.', ''),
                "influences_sale" => $influences_sale
            ]
        ];
    }



    public function updateAmountCancellation(int $id, array $params): bool
    {  
 
        $fileHotelRoomEloquent = FileHotelRoomEloquentModel::query()->with([
            'fileRoomAmountLogs', 
            'fileItinerary',
            'units.nights'
        ])->find($id);
            
        // Preoceso, crear registro en la tabla file_room_amount_logs, en esta tabla siempre habra 1 registro y si se crea uno nuevo se elimina el que estaba activo pero necesitamos recuperar el monto que tenia
        // actualmente el servicio actualizado

        $hotelRoomAmountLog = $fileHotelRoomEloquent->fileRoomAmountLogs->whereNull('deleted_at')->first();
    
        $fileRoomAmountLogEloquentModel = FileRoomAmountLogEloquentModel::create([
            'file_amount_type_flag_id' => $params['file_amount_type_flag_id']->value(), // abierto, cerrado, bloqueado
            'file_amount_reason_id' => $params['file_amount_reason_id']->value(), // Hotel exonera penalidad, Reserva migra a otro file, File asume penalidad 
            'executive_id' => $params['executive_id']->value(),  // exonera penalidad
            'file_id' => $params['file_id']->value(),  // exonera penalidad
            'motive' => $params['motive']->value(), // exonera penalidad
            'file_hotel_room_id' => $id, 
            'user_id' => $params['user_id']->value(),
            'amount_previous' => $hotelRoomAmountLog->amount, 
            'amount' => $params['amount_cost']->value(), 
        ]);

        $hotelRoomAmountLog->delete();

        // Si la razon de la actualizacion de monto influye a la venta tenemos que generar un registro en la tabla file_itinerary_service_amount_logs  y actualizar el campos total_amount(venta) del itinerario        
        $amountReason = FileAmountReasonEloquentModel::find($params['file_amount_reason_id']->value()); 

        if($amountReason->influences_sale == 1){

            $newServiceAmountCost = $params['amount_cost']->value();  // 120     80
           
            $serviceAmountCost = $fileHotelRoomEloquent->amount_sale; 

            $value = 0;

            if($serviceAmountCost < $newServiceAmountCost){  // le aumentamos 20 al precio de venta
               $diference = $newServiceAmountCost - $serviceAmountCost; 
               $value = $diference;
            }elseif($serviceAmountCost > $newServiceAmountCost){ // le restamos 20 al precio de venta
               $diference = $serviceAmountCost - $newServiceAmountCost; 
               $value = $diference * -1;
            }elseif($serviceAmountCost = $newServiceAmountCost){ // le restamos 20 al precio de venta
                $diference = $newServiceAmountCost; 
                $value = $diference;             
            }  
            
            // $fileHotelRoomEloquent->fileItinerary->save();
            
            FileItineraryRoomAmountLogEloquentModel::create([
                'file_itinerary_id' => $fileHotelRoomEloquent->fileItinerary->id, 
                'file_room_amount_log_id' => $fileRoomAmountLogEloquentModel->id, 
                'value' => $value,
                'markup' => $fileHotelRoomEloquent->fileItinerary->profitability
            ]);
        }

        // actualizamos los candados de todos los units y night
        foreach($fileHotelRoomEloquent->units as $unit){
            foreach($unit->nights as $night){
                $night->file_amount_type_flag_id =  $params['file_amount_type_flag_id']->value(); 
                $night->save();
            }
            $unit->file_amount_type_flag_id =  $params['file_amount_type_flag_id']->value(); 
            $unit->save();            
        }

        $fileHotelRoomEloquent = FileHotelRoomEloquentModel::query()->with(['units'])->find($id);
        $fileHotelRoomEloquent->file_amount_type_flag_id =  $params['file_amount_type_flag_id']->value();
        $fileHotelRoomEloquent->amount_sale = $fileHotelRoomEloquent->units->sum('amount_sale'); 
        $fileHotelRoomEloquent->save();

        return true;
    }

    
    public function findById(int $id): array
    {       
        $fileHotelRoomEloquent = FileHotelRoomEloquentModel::query()->with([
            'fileItinerary.file',
            'hotelRoomUnits',            
        ])->findOrFail($id);
 
        return $fileHotelRoomEloquent->toArray();
    }

    public function updateStatus(int $id, int $status): bool
    {  
        dd("debe de actualizar status root", $id);
 
        
        return true;
    }

    public function cancel(int $id): bool
    {  
        
        $fileHotelRoomEloquent = FileHotelRoomEloquentModel::query()->with([ 
            'hotelRoomUnits',            
        ])->find($id);
        
        $status = false;
        foreach($fileHotelRoomEloquent->hotelRoomUnits as $hotelRoomUnits){
            if($hotelRoomUnits->status == "1"){
               $status = true;
            }
        }    

        if($status == false){
            $fileHotelRoomEloquent->status = 0;
            $fileHotelRoomEloquent->save();
        }

        return true;
    }

    public function updateConfirmationCode(int $id, string $code): array
    {          
        $fileHotelRoomEloquent = FileHotelRoomEloquentModel::query()->with([ 
            'fileItinerary','hotelRoomUnits',            
        ])->find($id);
           
        $auto_order = NULL;        
        foreach($fileHotelRoomEloquent->fileItinerary->rooms as $index => $rooms)
        {
            if($rooms->id == $id)
            {
                $auto_order = ($index + 1);
            }    
        }

        if($auto_order == NULL){
            throw new \DomainException('auto_order does not exist');
        }

        $fileHotelRoomEloquent->confirmation_status = true;        
        $fileHotelRoomEloquent->waiting_list = false;
        $fileHotelRoomEloquent->confirmation_code = $code;
        $fileHotelRoomEloquent->save();

        foreach($fileHotelRoomEloquent->hotelRoomUnits as $hotelRoomUnits){
            $hotelRoomUnits->confirmation_status = true;        
            $hotelRoomUnits->waiting_list = false;
            $hotelRoomUnits->confirmation_code = $code;
            $hotelRoomUnits->save();                        
        }    
           

        // actualizaremos el itineario si es que todos los hotel rooms estan confirmados 
        $fileItineryEloquentModel = FileItineraryEloquentModel::query()->with(['rooms'])->find($fileHotelRoomEloquent->file_itinerary_id);
         
        $assing_all_confirmation_code = true;  
        foreach($fileItineryEloquentModel->rooms as $room)
        {
            if($room->confirmation_status == false)
            {
                $assing_all_confirmation_code = false;
            }
        }

        if($assing_all_confirmation_code == true)
        {
            $fileItineryEloquentModel->confirmation_status = true;    
            $fileItineryEloquentModel->save();
        } 


        return [
            "services" => [
                [
                    "code" => $fileHotelRoomEloquent->fileItinerary->object_code,
                    "date_in" => Carbon::parse($fileHotelRoomEloquent->fileItinerary->date_in)->format('d/m/Y'),
                    "auto_order" => $auto_order,
                    "confirmation_code" => $fileHotelRoomEloquent->confirmation_code
                ]
            ]
        ];
    }
 


}
