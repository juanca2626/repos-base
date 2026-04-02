<?php

namespace Src\Modules\File\Presentation\Http\Traits;

use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel;

trait ServiceAutoOrder
{
 
    public function getServiceAutoOrder($file_id, $service_id, $code , $date_in)
    { 

        $_master_services = FileServiceEloquentModel::select('id')->whereHas('fileItinerary.file', function ($query) use ($file_id) {
            $query->where('id', $file_id);
        });
        $_master_services->where('code', '=', $code);
        $_master_services->where('date_in', '=', $date_in);        
        $_master_services->orderBy('id'); 
        $_master_services = $_master_services->pluck('id')->toArray();      
        $auto_order = array_search($service_id, $_master_services) + 1;

        return $auto_order;

    }  
 
}
