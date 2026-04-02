<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
 
use Src\Modules\File\Domain\Repositories\StatusReasonRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\StatusReasonEloquentModel;

class StatusReasonRepository implements StatusReasonRepositoryInterface
{
    public function searchAll(array $filters): array
    {
        $statusReasonEloquentModel = StatusReasonEloquentModel::query();

        if($filters['status_iso'] and $filters['status_iso'] != "all"){
            $statusReasonEloquentModel->whereIn('status_iso', $filters['status_iso']);
        }

        if(isset($filters['visible']) and $filters['visible'] != "all"){
            
            $statusReasonEloquentModel->where('visible', $filters['visible']);
        }        
        
        $file_amount_reasons = $statusReasonEloquentModel->get()->toArray();

        return $file_amount_reasons;
    }
}
