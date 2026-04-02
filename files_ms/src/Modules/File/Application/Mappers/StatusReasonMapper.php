<?php

namespace Src\Modules\File\Application\Mappers;
 
use Src\Modules\File\Domain\Model\StatusReason;
use Src\Modules\File\Domain\ValueObjects\StatusReason\Name; 
use Src\Modules\File\Domain\ValueObjects\StatusReason\Visible;
use Src\Modules\File\Domain\ValueObjects\StatusReason\StatusIso;  
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\StatusReasonEloquentModel;

class StatusReasonMapper
{
    public static function fromRequestSearch($request): array
    {
        $page = (int) $request->has('page') ? $request->page : 1;
        $per_page = (int) ($request->has('per_page')) ? $request->input('per_page') : 10;
        $filter = (string) $request->has('filter') ? $request->filter : '';
        $status_iso = (string) $request->has('status_iso') ? $request->status_iso : ''; 
        $visible = (string) $request->has('visible') ? $request->visible : 1;
        
        return [
            'page' => $page,
            'per_page' => $per_page,
            'filter' => $filter,
            'status_iso' => $status_iso, 
            'visible' => $visible,
        ];
    }

    public static function fromArray(array $statusReason): StatusReason
    {
        $statusReasonEloquentModel = new StatusReasonEloquentModel($statusReason);
        $statusReasonEloquentModel->id = $statusReason['id'] ?? null;
  
        return self::fromEloquent($statusReasonEloquentModel);
    }


    public static function fromEloquent(StatusReasonEloquentModel $statusReasonEloquentModel): StatusReason
    {
        return new StatusReason(
            id: $statusReasonEloquentModel->id, 
            statusIso: new StatusIso($statusReasonEloquentModel->status_iso),  
            name: new Name($statusReasonEloquentModel->name), 
            visible: new Visible($statusReasonEloquentModel->visible),            
        );
    }

    public static function toEloquent(StatusReason $statusReason): StatusReasonEloquentModel
    {
        $statusReasonEloquentModel = null;

        if($statusReason->id)
        {
            $statusReasonEloquentModel = StatusReasonEloquentModel::query(); 
            $statusReasonEloquentModel = $statusReasonEloquentModel->where('id', '=', $statusReason->id);
            $statusReasonEloquentModel = $statusReasonEloquentModel->first();
        }
 
        if(!$statusReasonEloquentModel)
        {
            $statusReasonEloquentModel = new StatusReasonEloquentModel();
        }
 
        $statusReasonEloquentModel->name = $statusReason->name->value();
        
        return $statusReasonEloquentModel;
    }
}
