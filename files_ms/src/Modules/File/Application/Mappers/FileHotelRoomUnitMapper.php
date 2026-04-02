<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileHotelRoomUnit;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\AdultNum;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\Status;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileHotelRoomId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\AmountSale; 
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ChannelId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ChannelReservationCode;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ChildNum;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ConfirmationCode;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ExtraNum;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileHotelRoomUnitNights;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileRoomAccommodations;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\TaxedUnitCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\TaxedUnitSale;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileHotelRoomUnitId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\InfantNum;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\Markup;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\Padlock;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\PoliciesCancellation;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\RatesPlansRoomsId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ReservationsRatesPlansRoomsId; 
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\TaxesAndService;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomUnitEloquentModel;

class FileHotelRoomUnitMapper
{
    public static function fromArray(array $fileHotelRoomUnit): FileHotelRoomUnit
    {
  
        $fileHotelRoomUnitEloquentModel = new FileHotelRoomUnitEloquentModel($fileHotelRoomUnit);
        $fileHotelRoomUnitEloquentModel->id = $fileHotelRoomUnit['id'] ?? null;
        $fileHotelRoomUnitEloquentModel->nights = collect();
        $fileHotelRoomUnitEloquentModel->accommodations = collect();
        $fileHotelRoomUnitEloquentModel->file_hotel_room = collect();

        if (isset($fileHotelRoomUnit['nights']) &&
            count($fileHotelRoomUnit['nights']) != $fileHotelRoomUnitEloquentModel->nights->count()) {
            foreach ($fileHotelRoomUnit['nights'] as $hotelRoomUnitNight) {
                $fileHotelRoomUnitEloquentModel->nights->add($hotelRoomUnitNight);
            }
        }
   
        if (isset($fileHotelRoomUnit['accommodations'])) {
            foreach ($fileHotelRoomUnit['accommodations'] as $hotelRoomUnitAccommodation) {
                $fileHotelRoomUnitEloquentModel->accommodations->add($hotelRoomUnitAccommodation);
            }
        }

        if (isset($fileHotelRoomUnit['file_hotel_room'])) {
            $fileHotelRoomUnitEloquentModel->file_hotel_room = collect($fileHotelRoomUnit['file_hotel_room']); 
        }

        return self::fromEloquent($fileHotelRoomUnitEloquentModel);
    }

    public static function fromEloquent(FileHotelRoomUnitEloquentModel $fileHotelRoomUnitEloquentModel
    ): FileHotelRoomUnit {
        // dd($fileHotelRoomUnitEloquentModel->accommodations);
        $nights = array_map(function ($nights) {
            return FileHotelRoomUnitNightMapper::fromArray($nights);
        }, $fileHotelRoomUnitEloquentModel->nights?->toArray() ?? []);

        $accommodations = array_map(function ($accommodations) {
            return FileRoomAccommodationMapper::fromArray($accommodations);
        }, $fileHotelRoomUnitEloquentModel->accommodations?->toArray() ?? []); 
        
        $markup = 0;
        if($fileHotelRoomUnitEloquentModel->file_hotel_room?->toArray()) {
            $markup = $fileHotelRoomUnitEloquentModel->file_hotel_room['markup_created'];
        }

        return new FileHotelRoomUnit(
            id: new FileHotelRoomUnitId($fileHotelRoomUnitEloquentModel->id),
            fileHotelRoomId: new FileHotelRoomId($fileHotelRoomUnitEloquentModel->file_hotel_room_id),
            status: new Status($fileHotelRoomUnitEloquentModel->status),
            channelId: new ChannelId($fileHotelRoomUnitEloquentModel->channel_id),
            confirmationCode: new ConfirmationCode($fileHotelRoomUnitEloquentModel->confirmation_code),
            amountSale: new AmountSale($fileHotelRoomUnitEloquentModel->amount_sale),
            amountCost: new AmountCost($fileHotelRoomUnitEloquentModel->amount_cost),
            taxedUnitSale: new TaxedUnitSale($fileHotelRoomUnitEloquentModel->taxed_unit_sale),
            taxedUnitCost: new TaxedUnitCost($fileHotelRoomUnitEloquentModel->taxed_unit_cost),
            adultNum: new AdultNum($fileHotelRoomUnitEloquentModel->adult_num),
            childNum: new ChildNum($fileHotelRoomUnitEloquentModel->child_num),
            infantNum: new InfantNum($fileHotelRoomUnitEloquentModel->infant_num),
            extraNum: new ExtraNum($fileHotelRoomUnitEloquentModel->extra_num),
            reservationsRatesPlansRoomsId: new ReservationsRatesPlansRoomsId(
                $fileHotelRoomUnitEloquentModel->reservations_rates_plans_rooms_id
            ),
            ratesPlansRoomsId: new RatesPlansRoomsId(
                $fileHotelRoomUnitEloquentModel->rates_plans_rooms_id
            ),                        
            channelReservationCode: new ChannelReservationCode(
                $fileHotelRoomUnitEloquentModel->channel_reservation_code
            ),
            confirmationStatus: new ConfirmationStatus($fileHotelRoomUnitEloquentModel->confirmation_status),
            policiesCancellation: new PoliciesCancellation($fileHotelRoomUnitEloquentModel->policies_cancellation),
            taxesAndService: new TaxesAndService($fileHotelRoomUnitEloquentModel->taxes_and_services),
            nights: new FileHotelRoomUnitNights($nights),
            accommodations: new FileRoomAccommodations($accommodations),
            fileAmountTypeFlagId: new FileAmountTypeFlagId($fileHotelRoomUnitEloquentModel->file_amount_type_flag_id),
            markup: new Markup($markup)
        );
    }

    public static function toEloquent(FileHotelRoomUnit $fileHotelRoomUnit): FileHotelRoomUnitEloquentModel
    {
        $fileHotelRoomUnitEloquent = new FileHotelRoomUnitEloquentModel();
        if ($fileHotelRoomUnit->id->value()) {
            $fileHotelRoomUnitEloquent = FileHotelRoomUnitEloquentModel::query()
                ->findOrFail($fileHotelRoomUnit->id->value());
        }

        $fileHotelRoomUnitEloquent->file_hotel_room_id = $fileHotelRoomUnit->fileHotelRoomId->value();
        $fileHotelRoomUnitEloquent->channel_id = $fileHotelRoomUnit->channelId->value();
        $fileHotelRoomUnitEloquent->status = $fileHotelRoomUnit->status->value();
        $fileHotelRoomUnitEloquent->confirmation_status = $fileHotelRoomUnit->confirmationStatus->value();
        $fileHotelRoomUnitEloquent->confirmation_code = $fileHotelRoomUnit->confirmationCode->value();
        $fileHotelRoomUnitEloquent->amount_sale = $fileHotelRoomUnit->amountSale->value();
        $fileHotelRoomUnitEloquent->amount_cost = $fileHotelRoomUnit->amountCost->value();
        $fileHotelRoomUnitEloquent->taxed_unit_sale = $fileHotelRoomUnit->taxedUnitSale->value();
        $fileHotelRoomUnitEloquent->taxed_unit_cost = $fileHotelRoomUnit->taxedUnitCost->value();
        $fileHotelRoomUnitEloquent->adult_num = $fileHotelRoomUnit->adultNum->value();
        $fileHotelRoomUnitEloquent->child_num = $fileHotelRoomUnit->childNum->value();
        $fileHotelRoomUnitEloquent->infant_num = $fileHotelRoomUnit->infantNum->value();
        $fileHotelRoomUnitEloquent->extra_num = $fileHotelRoomUnit->extraNum->value();
        $fileHotelRoomUnitEloquent->reservations_rates_plans_rooms_id = $fileHotelRoomUnit->reservationsRatesPlansRoomsId->value();
        $fileHotelRoomUnitEloquent->rates_plans_rooms_id = $fileHotelRoomUnit->ratesPlansRoomsId->value();
        $fileHotelRoomUnitEloquent->channel_reservation_code = $fileHotelRoomUnit->channelReservationCode->value();
        $fileHotelRoomUnitEloquent->policies_cancellation = $fileHotelRoomUnit->policiesCancellation->value();
        $fileHotelRoomUnitEloquent->taxes_and_services = $fileHotelRoomUnit->taxesAndService->value();
        $fileHotelRoomUnitEloquent->file_amount_type_flag_id = $fileHotelRoomUnit->fileAmountTypeFlagId->value();
        return $fileHotelRoomUnitEloquent;
    }
}
