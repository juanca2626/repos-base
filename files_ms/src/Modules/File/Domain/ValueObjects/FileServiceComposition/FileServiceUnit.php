<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileServiceUnit extends ValueObjectArray
{
    public readonly array $units;

    public function __construct(array $units)
    {
        parent::__construct($units);
        $this->units = $units;
    }
    
    public function getUnits(): FileServiceUnit
    {
        return new FileServiceUnit($this->units);
    }

    public function toArray(): array
    {
        $itineraryServiceCompositionUnits = [];

        foreach($this->units as $unit){
          
            array_push($itineraryServiceCompositionUnits, [
                'id' => $unit->id,
                'file_service_composition_id' => $unit->fileServiceCompositionId->value(),
                'status' => $unit->status->value(),
                'amount_sale' => $unit->amountSale->value(),
                'amount_cost' => $unit->amountCost->value(),
                'amount_sale_origin' => $unit->amountSaleOrigin->value(),
                'amount_cost_origin' => $unit->amountCostOrigin->value(),
                'accommodations' => $unit->accommodations
            ]);
        }

        return $itineraryServiceCompositionUnits;

    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->units;
    }
}
