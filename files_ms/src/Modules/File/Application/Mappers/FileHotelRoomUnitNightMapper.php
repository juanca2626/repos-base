<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileHotelRoomUnit;
use Src\Modules\File\Domain\Model\FileHotelRoomUnitNight;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileHotelRoomUnitId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\Date;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\FileHotelRoomUnitNightId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\Number;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight\Padlock;
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
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomUnitNightEloquentModel;

class FileHotelRoomUnitNightMapper
{
    public static function fromArray(array $fileHotelRoomUnitNight): FileHotelRoomUnitNight
    {
        $fileHotelRoomUnitNightEloquentModel = new FileHotelRoomUnitNightEloquentModel($fileHotelRoomUnitNight);
        $fileHotelRoomUnitNightEloquentModel->id = $fileHotelRoomUnitNight['id'] ?? null;
        return self::fromEloquent($fileHotelRoomUnitNightEloquentModel);
    }

    public static function fromEloquent(FileHotelRoomUnitNightEloquentModel $fileHotelRoomUnitEloquentModel
    ): FileHotelRoomUnitNight {
        return new FileHotelRoomUnitNight(
            id: new FileHotelRoomUnitNightId($fileHotelRoomUnitEloquentModel->id),
            fileHotelRoomUnitId: new FileHotelRoomUnitId($fileHotelRoomUnitEloquentModel->file_hotel_room_unit_id),
            date: new Date($fileHotelRoomUnitEloquentModel->date),
            number: new Number($fileHotelRoomUnitEloquentModel->number),
            priceAdultSale: new PriceAdultSale($fileHotelRoomUnitEloquentModel->price_adult_sale),
            priceAdultCost: new PriceAdultCost($fileHotelRoomUnitEloquentModel->price_adult_cost),
            priceChildSale: new PriceChildSale($fileHotelRoomUnitEloquentModel->price_child_sale),
            priceChildCost: new PriceChildCost($fileHotelRoomUnitEloquentModel->price_child_cost),
            priceInfantSale: new PriceInfantSale($fileHotelRoomUnitEloquentModel->price_infant_sale),
            priceInfantCost: new PriceInfantCost($fileHotelRoomUnitEloquentModel->price_infant_cost),
            priceExtraSale: new PriceExtraSale($fileHotelRoomUnitEloquentModel->price_extra_sale),
            priceExtraCost: new PriceExtraCost($fileHotelRoomUnitEloquentModel->price_extra_cost),
            totalAmountSale: new TotalAmountSale($fileHotelRoomUnitEloquentModel->total_amount_sale),
            totalAmountCost: new TotalAmountCost($fileHotelRoomUnitEloquentModel->total_amount_cost),
            fileAmountTypeFlagId: new FileAmountTypeFlagId($fileHotelRoomUnitEloquentModel->file_amount_type_flag_id)
        );
    }

    public static function toEloquent(FileHotelRoomUnitNight $fileHotelRoomUnitNight): FileHotelRoomUnitNightEloquentModel
    {
        $fileHotelRoomUnitNightEloquent = new FileHotelRoomUnitNightEloquentModel();
        if ($fileHotelRoomUnitNight->id->value()) {
            $fileHotelRoomUnitNightEloquent = FileHotelRoomUnitNightEloquentModel::query()->findOrFail($fileHotelRoomUnitNight->id->value());
        }
        $fileHotelRoomUnitNightEloquent->file_hotel_room_unit_id = $fileHotelRoomUnitNight->fileHotelRoomUnitId->value();
        $fileHotelRoomUnitNightEloquent->date = $fileHotelRoomUnitNight->date->value();
        $fileHotelRoomUnitNightEloquent->number = $fileHotelRoomUnitNight->number->value();
        $fileHotelRoomUnitNightEloquent->price_adult_sale = $fileHotelRoomUnitNight->priceAdultSale->value();
        $fileHotelRoomUnitNightEloquent->price_adult_cost = $fileHotelRoomUnitNight->priceAdultCost->value();
        $fileHotelRoomUnitNightEloquent->price_child_sale = $fileHotelRoomUnitNight->priceChildSale->value();
        $fileHotelRoomUnitNightEloquent->price_child_cost = $fileHotelRoomUnitNight->priceChildCost->value();
        $fileHotelRoomUnitNightEloquent->price_infant_sale = $fileHotelRoomUnitNight->priceInfantSale->value();
        $fileHotelRoomUnitNightEloquent->price_infant_cost = $fileHotelRoomUnitNight->priceInfantCost->value();
        $fileHotelRoomUnitNightEloquent->price_extra_sale = $fileHotelRoomUnitNight->priceExtraSale->value();
        $fileHotelRoomUnitNightEloquent->price_extra_cost = $fileHotelRoomUnitNight->priceExtraCost->value();
        $fileHotelRoomUnitNightEloquent->total_amount_sale = $fileHotelRoomUnitNight->totalAmountSale->value();
        $fileHotelRoomUnitNightEloquent->total_amount_cost = $fileHotelRoomUnitNight->totalAmountCost->value();
        $fileHotelRoomUnitNightEloquent->file_amount_type_flag_id = $fileHotelRoomUnitNight->fileAmountTypeFlagId->value();
        return $fileHotelRoomUnitNightEloquent;
    }
}
