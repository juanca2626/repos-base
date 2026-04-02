<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition\FileTemporaryServiceCompositionId; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceUnit\Accommodation;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceUnit\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceUnit\AmountSale;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceUnit\Status;
use Src\Shared\Domain\Entity;

class FileTemporaryServiceUnit extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly FileTemporaryServiceCompositionId $fileTemporaryServiceCompositionId,
        public readonly Status $status,
        public readonly AmountSale $amountSale,
        public readonly AmountCost $amountCost,
        public readonly AmountSale $amountSaleOrigin,
        public readonly AmountCost $amountCostOrigin, 
        public readonly Accommodation $accommodations,
        
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_temporary_service_composition_id' => $this->fileTemporaryServiceCompositionId->value(),
            'status' => $this->status->value(),
            'amount_sale' => $this->amountSale->value(),
            'amount_cost' => $this->amountCost->value(),
            'amount_sale_origin' => $this->amountSaleOrigin->value(),
            'amount_cost_origin' => $this->amountCostOrigin->value(), 
            'accommodations' => $this->accommodations->jsonSerialize(),
        ];
    }

}
