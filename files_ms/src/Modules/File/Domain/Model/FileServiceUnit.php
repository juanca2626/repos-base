<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileServiceComposition\FileServiceCompositionId;
use Src\Modules\File\Domain\ValueObjects\FileServiceUnit\Accommodation;
use Src\Modules\File\Domain\ValueObjects\FileServiceUnit\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileServiceUnit\AmountSale;
use Src\Modules\File\Domain\ValueObjects\FileServiceUnit\Status;
use Src\Shared\Domain\Entity;

class FileServiceUnit extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly FileServiceCompositionId $fileServiceCompositionId,
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
            'file_service_composition_id' => $this->fileServiceCompositionId->value(),
            'status' => $this->status->value(),
            'amount_sale' => $this->amountSale->value(),
            'amount_cost' => $this->amountCost->value(),
            'amount_sale_origin' => $this->amountSaleOrigin->value(),
            'amount_cost_origin' => $this->amountCostOrigin->value(),
            'accommodations' => $this->accommodations->jsonSerialize(),
        ];
    }

}
