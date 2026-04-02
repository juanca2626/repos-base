<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileCategory;
use Src\Modules\File\Domain\Model\FileStatusReason;
use Src\Modules\File\Domain\ValueObjects\FileStatusReason\Status;
use Src\Modules\File\Domain\ValueObjects\FileStatusReason\StatusReasonId;
use Src\Modules\File\Domain\ValueObjects\FileStatusReason\FileId; 
use Src\Modules\File\Domain\ValueObjects\FileStatusReason\StatusReason; 
use Src\Modules\File\Domain\ValueObjects\FileStatusReason\CreatedAt; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCategoryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileStatusReasonEloquentModel;

use function PHPSTORM_META\map;

class FileStatusReasonMapper
{
     
    public static function fromArray(array $fileStatusReason): FileStatusReason
    {
        $fileStatusReasonEloquentModel = new FileStatusReasonEloquentModel($fileStatusReason);
        $fileStatusReasonEloquentModel->id = $fileStatusReason['id'] ?? null;
        
        return self::fromEloquent($fileStatusReasonEloquentModel);
    }

    public static function fromEloquent(FileStatusReasonEloquentModel $fileStatusReasonEloquentModel
    ): FileStatusReason {

        $statusReason = collect();      
        if($fileStatusReasonEloquentModel->statusReason?->toArray()){
           $statusReason = $fileStatusReasonEloquentModel->statusReason?->toArray();
           $statusReason = StatusReasonMapper::fromArray($statusReason);
        }

        return new FileStatusReason(
            id: $fileStatusReasonEloquentModel->id,
            fileId: new FileId($fileStatusReasonEloquentModel->file_id),
            status: new Status($fileStatusReasonEloquentModel->status),
            statusReasonId: new StatusReasonId($fileStatusReasonEloquentModel->status_reason_id),            
            createdAt: new CreatedAt($fileStatusReasonEloquentModel->created_at),
            statusReason: new StatusReason($statusReason)  
        );
    }

    public static function toEloquent(FileStatusReason
        $fileStatusReason): FileStatusReasonEloquentModel
    {
        $fileStatusReasonEloquentModel = new FileStatusReasonEloquentModel();
        if ($fileStatusReason->id) {
            $fileStatusReasonEloquentModel = FileStatusReasonEloquentModel::query()
                ->findOrFail($fileStatusReason->id);
        }
                
        $fileStatusReasonEloquentModel->file_id = $fileStatusReason->fileId->value();
        $fileStatusReasonEloquentModel->status = $fileStatusReason->status->value(); 
        $fileStatusReasonEloquentModel->status_reason_id = $fileStatusReason->statusReasonId->value(); 
        $fileStatusReasonEloquentModel->created_at = $fileStatusReason->createdAt->value();
        return $fileStatusReasonEloquentModel;
    }

}
