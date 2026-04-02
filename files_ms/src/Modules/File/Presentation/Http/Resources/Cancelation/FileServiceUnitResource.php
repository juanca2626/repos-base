<?php

namespace Src\Modules\File\Presentation\Http\Resources\Cancelation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileServiceUnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'file_service_composition_id' => $this->resource->fileServiceCompositionId->value(),
            'cancellation_status' => $this->resource->cancellationStatus->value(),
            'amount_sale' => $this->resource->amountSale->value(),
            'amount_cost' => $this->resource->amountCost->value(),
            'amount_sale_origin' => $this->resource->amountSaleOrigin->value(),
            'amount_cost_origin' => $this->resource->amountCostOrigin->value(),
        ];
    }
}
 