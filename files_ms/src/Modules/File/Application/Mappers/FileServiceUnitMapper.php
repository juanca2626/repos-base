<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\FileServiceUnit;
use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\FileServiceCompositionId;
use Src\Modules\File\Domain\ValueObjects\FileServiceUnit\Accommodation;
use Src\Modules\File\Domain\ValueObjects\FileServiceUnit\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileServiceUnit\AmountSale;
use Src\Modules\File\Domain\ValueObjects\FileServiceUnit\Status; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceUnitEloquentModel;

class FileServiceUnitMapper
{
    public static function fromRequestCreate(array $unit): FileServiceUnit
    {
        $file_service_composition_id = isset($unit['file_service_composition_id']) ? $unit['file_service_composition_id'] : 0;
        $status = $unit['status'] ? $unit['status'] :  1;
        $amount_sale = $unit['amount_sale'] ? $unit['amount_sale'] :  0;
        $amount_cost = $unit['amount_cost'] ? $unit['amount_cost'] :  0;
        $amount_sale_origin = $unit['amount_sale_origin'] ? $unit['amount_sale_origin'] :  0;
        $amount_cost_origin = $unit['amount_cost_origin'] ? $unit['amount_cost_origin'] :  0;
        $accommodations_array = $unit['accommodations'] ? $unit['accommodations'] :  [];

        $accommodations = array_map(function ($accommodations) {
            return FileServiceAccommodationMapper::fromRequestCreate($accommodations);
        }, $accommodations_array); 

        return new FileServiceUnit(
            id: NULL,
            fileServiceCompositionId: new FileServiceCompositionId($file_service_composition_id),
            status: new Status($status),
            amountSale: new AmountSale($amount_sale),
            amountCost: new AmountCost($amount_cost),
            amountSaleOrigin: new AmountSale($amount_sale_origin),
            amountCostOrigin: new AmountCost($amount_cost_origin),
            accommodations: new Accommodation($accommodations)        
        );
    }
 
    public static function fromArray($fileServiceUnit): FileServiceUnit
    {       
        $fileServiceUnitEloquentModel = new FileServiceUnitEloquentModel($fileServiceUnit);
        $fileServiceUnitEloquentModel->id = $fileServiceUnit['id'] ?? null; 
        
        if (isset($fileServiceUnit['accommodations'])) {
            $fileServiceUnitEloquentModel->accommodations = collect();
            foreach($fileServiceUnit['accommodations'] as $unit) {
                $fileServiceUnitEloquentModel->accommodations->add($unit);
            }
        }

        return self::fromEloquent($fileServiceUnitEloquentModel);
    }

    public static function fromEloquent(FileServiceUnitEloquentModel $fileServiceUnitEloquentModel): FileServiceUnit
    {           
        $accommodations = array_map(function ($accommodations) {
            return FileServiceAccommodationMapper::fromArray($accommodations);
        }, $fileServiceUnitEloquentModel->accommodations?->toArray() ?? []); 

        return new FileServiceUnit(
            id: $fileServiceUnitEloquentModel->id,
            fileServiceCompositionId: new FileServiceCompositionId($fileServiceUnitEloquentModel->file_service_composition_id),
            status: new Status($fileServiceUnitEloquentModel->status),
            amountSale: new AmountSale($fileServiceUnitEloquentModel->amount_sale),
            amountCost: new AmountCost($fileServiceUnitEloquentModel->amount_cost),
            amountSaleOrigin: new AmountSale($fileServiceUnitEloquentModel->amount_sale_origin),
            amountCostOrigin: new AmountCost($fileServiceUnitEloquentModel->amount_cost_origin),
            accommodations: new Accommodation($accommodations)        
        );
    }

    public static function toEloquent(FileServiceUnit $fileServiceUnit): FileServiceUnitEloquentModel
    {
        $fileServiceUnitEloquent = new FileServiceUnitEloquentModel();
        if ($fileServiceUnit->id) {
            $fileServiceUnitEloquent = FileServiceUnitEloquentModel::query()->findOrFail($fileServiceUnit->id);
        }
        $fileServiceUnitEloquent->file_service_composition_id = $fileServiceUnit->fileServiceCompositionId->value();
        $fileServiceUnitEloquent->status = $fileServiceUnit->status->value();
        $fileServiceUnitEloquent->amount_sale = $fileServiceUnit->amountSale->value();
        $fileServiceUnitEloquent->amount_cost = $fileServiceUnit->amountCost->value();
        $fileServiceUnitEloquent->amount_sale_origin = $fileServiceUnit->amountSaleOrigin->value();
        $fileServiceUnitEloquent->amount_cost_origin = $fileServiceUnit->amountCostOrigin->value(); 
  
        return $fileServiceUnitEloquent;
    }
      
}
