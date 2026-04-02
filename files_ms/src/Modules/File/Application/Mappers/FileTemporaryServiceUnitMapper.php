<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request; 
use Src\Modules\File\Domain\Model\FileTemporaryServiceUnit;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\FileTemporaryServiceCompositionId; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceUnit\Accommodation;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceUnit\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceUnit\AmountSale;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceUnit\Status;  
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryServiceUnitEloquentModel;

class FileTemporaryServiceUnitMapper
{
    public static function fromArray($fileServiceUnit): FileTemporaryServiceUnit
    {       
        $fileServiceUnitEloquentModel = new FileTemporaryServiceUnitEloquentModel($fileServiceUnit);
        $fileServiceUnitEloquentModel->id = $fileServiceUnit['id'] ?? null; 
        $fileServiceUnitEloquentModel->passengers = collect();

        return self::fromEloquent($fileServiceUnitEloquentModel);
    }

    public static function fromEloquent(FileTemporaryServiceUnitEloquentModel $fileServiceUnitEloquentModel): FileTemporaryServiceUnit
    {           
        // $accommodations = array_map(function ($accommodations) {
        //     return FileServiceAccommodationMapper::fromArray($accommodations);
        // }, $fileServiceUnitEloquentModel->accommodations?->toArray() ?? []); 

        $accommodations = []; 
        return new FileTemporaryServiceUnit(
            id: $fileServiceUnitEloquentModel->id,
            fileTemporaryServiceCompositionId: new FileTemporaryServiceCompositionId($fileServiceUnitEloquentModel->file_temporary_service_composition_id),
            status: new Status($fileServiceUnitEloquentModel->status),
            amountSale: new AmountSale($fileServiceUnitEloquentModel->amount_sale),
            amountCost: new AmountCost($fileServiceUnitEloquentModel->amount_cost),
            amountSaleOrigin: new AmountSale($fileServiceUnitEloquentModel->amount_sale_origin),
            amountCostOrigin: new AmountCost($fileServiceUnitEloquentModel->amount_cost_origin), 
            accommodations: new Accommodation($accommodations)         
        );
    }

    public static function toEloquent(FileTemporaryServiceUnit $fileServiceUnit): FileTemporaryServiceUnitEloquentModel
    {
        $fileServiceUnitEloquent = new FileTemporaryServiceUnitEloquentModel();
        if ($fileServiceUnit->id) {
            $fileServiceUnitEloquent = FileTemporaryServiceUnitEloquentModel::query()->findOrFail($fileServiceUnit->id);
        }
        $fileServiceUnitEloquent->file_temporary_service_composition_id = $fileServiceUnit->fileTemporaryServiceCompositionId->value();
        $fileServiceUnitEloquent->status = $fileServiceUnit->status->value();
        $fileServiceUnitEloquent->amount_sale = $fileServiceUnit->amountSale->value();
        $fileServiceUnitEloquent->amount_cost = $fileServiceUnit->amountCost->value();
        $fileServiceUnitEloquent->amount_sale_origin = $fileServiceUnit->amountSaleOrigin->value();
        $fileServiceUnitEloquent->amount_cost_origin = $fileServiceUnit->amountCostOrigin->value(); 
  
        return $fileServiceUnitEloquent;
    }
      
}
