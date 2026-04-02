<?php

namespace Src\Modules\File\Domain\Model;


use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileHotelRoomUnitId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\FileHotelRoomUnitNightId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\Date;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\Number;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\PriceAdultCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\PriceAdultSale;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\PriceChildCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\PriceChildSale;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\PriceExtraCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\PriceExtraSale;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\PriceInfantCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\PriceInfantSale;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\TotalAmountCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\TotalAmountSale;
use Src\Shared\Domain\Entity;

class FileHotelRoomUnitNight extends Entity
{
    public function __construct(
        public readonly FileHotelRoomUnitNightId $id,
        public readonly FileHotelRoomUnitId $fileHotelRoomUnitId,
        public readonly Date $date,
        public readonly Number $number,
        public readonly PriceAdultSale $priceAdultSale,
        public readonly PriceAdultCost $priceAdultCost,
        public readonly PriceChildSale $priceChildSale,
        public readonly PriceChildCost $priceChildCost,
        public readonly PriceInfantSale $priceInfantSale,
        public readonly PriceInfantCost $priceInfantCost,
        public readonly PriceExtraSale $priceExtraSale,
        public readonly PriceExtraCost $priceExtraCost,
        public readonly TotalAmountSale $totalAmountSale,
        public readonly TotalAmountCost $totalAmountCost,
        public readonly FileAmountTypeFlagId $fileAmountTypeFlagId 
        
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_hotel_room_unit_id' => $this->fileHotelRoomUnitId->value(),
            'date' => $this->date->value(),
            'number' => $this->number->value(),
            'price_adult_sale' => $this->priceAdultSale->value(),
            'price_adult_cost' => $this->priceAdultCost->value(),
            'price_child_sale' => $this->priceChildSale->value(),
            'price_child_cost' => $this->priceChildCost->value(),
            'price_infant_sale' => $this->priceInfantSale->value(),
            'price_infant_cost' => $this->priceInfantCost->value(),
            'price_extra_sale' => $this->priceExtraSale->value(),
            'price_extra_cost' => $this->priceExtraCost->value(),
            'total_amount_sale' => $this->totalAmountSale->value(),
            'total_amount_cost' => $this->totalAmountCost->value(),
            'file_amount_type_flag_id' => $this->fileAmountTypeFlagId->value()
        ];
    }

}
