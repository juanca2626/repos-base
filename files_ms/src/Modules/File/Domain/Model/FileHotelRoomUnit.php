<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\AdultNum;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileHotelRoomUnitId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileRoomAccommodations;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\AmountCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\AmountSale;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ChannelId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ChannelReservationCode;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ChildNum;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ConfirmationCode;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ExtraNum;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileAmountTypeFlagId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileHotelRoomId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileHotelRoomUnitNights;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\InfantNum;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\Markup;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\PoliciesCancellation;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\RatesPlansRoomsId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\ReservationsRatesPlansRoomsId;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\Status;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\TaxedUnitCost;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\TaxedUnitSale;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\TaxesAndService;
use Src\Shared\Domain\Entity;

class FileHotelRoomUnit extends Entity
{
    public function __construct(
        public readonly FileHotelRoomUnitId $id,
        public readonly FileHotelRoomId $fileHotelRoomId,
        public readonly Status $status,
        public readonly ChannelId $channelId,
        public readonly ConfirmationCode $confirmationCode,
        public readonly AmountSale $amountSale,
        public readonly AmountCost $amountCost,
        public readonly TaxedUnitSale $taxedUnitSale,
        public readonly TaxedUnitCost $taxedUnitCost,
        public readonly AdultNum $adultNum,
        public readonly ChildNum $childNum,
        public readonly InfantNum $infantNum,
        public readonly ExtraNum $extraNum,
        public readonly ReservationsRatesPlansRoomsId $reservationsRatesPlansRoomsId,
        public readonly RatesPlansRoomsId $ratesPlansRoomsId,        
        public readonly ChannelReservationCode $channelReservationCode,
        public readonly ConfirmationStatus $confirmationStatus,
        public readonly PoliciesCancellation $policiesCancellation,
        public readonly TaxesAndService $taxesAndService,
        public readonly FileHotelRoomUnitNights $nights,
        public readonly FileRoomAccommodations $accommodations,
        public readonly FileAmountTypeFlagId $fileAmountTypeFlagId,
        public readonly Markup $markup 
        
    ) {
    }


    public function calculatePenalty(): array
    {
 
        $penalidad = [
            'codigoReserva' => $this->reservationsRatesPlansRoomsId->value(),
            'idDetalleSvs' => $this->reservationsRatesPlansRoomsId->value(),
            'totalCosto' => 0,
            'totalIgv' => 0,
            'totalVenta' => 0,
            'notasEjecutiva' => '',
            'tipoCancel' => 'CANCEL',
            'tipo' => 'OK',
        ];

        $igv = [
            'percent' => 0,
            'total_amount' => 0,
        ];
        $extra_fees = json_decode($this->taxesAndService->value(), true);
        if (isset($extra_fees['apply_fees']) && isset($extra_fees['apply_fees']['t'])) {
            foreach ($extra_fees['apply_fees']['t'] as $extra_fee) {
                if ($extra_fee['name'] == 'IGV') {
                    $igv['percent'] = $extra_fee['value'];
                    $igv['total_amount'] = $extra_fee['total_amount'];
                }
            }
        }

        $policies_cancellation = json_decode($this->policiesCancellation->value(), true);
        if(isset($policies_cancellation) and count($policies_cancellation)>0){
            $policy_cancellation = (object)$policies_cancellation[0];
     
            $_apply_date = explode('-', $policy_cancellation->apply_date);
            $apply_date = $_apply_date[2].'-'.$_apply_date[1].'-'.$_apply_date[0];
            
            // 2021-08-05 < hoy (hoy quieres cancelar, cuando la fecha ya pasó) && ['onRequest'] != 0 (osea es OK) && no fue actualizado el ok desde stella
            if ($apply_date < date("Y-m-d") && $this->confirmationStatus->value() != 0) // $this->status->value() != 0 && $this->confirmationStatus->value() == 0
            {         
                $penalty_price = str_replace(',', '', $policy_cancellation->penalty_price);                
                // bug aqui "penalty_price" esta trayendo formateado 1,000.00 cuando son miles y generar errores con suma por es se elimino todas las ","
                $penalty_price = round($penalty_price, 2);
                $igv = round($igv['total_amount'], 2);
    
                $penalidad['totalCosto'] =  $penalty_price;
                $penalidad['totalIgv'] = $igv;
                $penalidad['totalVenta'] = $penalty_price;
                // $penalidad['totalVenta'] = $igv + $penalty_price;
                $penalidad['tipoCancel'] = 'GASCAN';
                
            }
        }
 
        return $penalidad ;
    }

    public function getPenalty(): array
    {
        $penality = $this->calculatePenalty();

        $penalty_cost = $penality['totalVenta'];
        if($this->markup->value()>0){
            $penalty_cost =  ($penality['totalVenta'] / (($this->markup->value() / 100) + 1));
        }
        return [
            'penalty_cost' => $penalty_cost,
            'penalty_sale' => $penality['totalVenta']
        ];
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_hotel_room_id' => $this->fileHotelRoomId->value(),
            'status' => $this->status->value(),
            'channel_id' => $this->channelId->value(),
            'confirmation_code' => $this->confirmationCode->value(),
            'amount_sale' => $this->amountSale->value(),
            'amount_cost' => $this->amountCost->value(),
            'taxed_unit_sale' => $this->taxedUnitSale->value(),
            'taxed_unit_cost' => $this->taxedUnitCost->value(),
            'adult_num' => $this->adultNum->value(),
            'child_num' => $this->childNum->value(),
            'infant_num' => $this->infantNum->value(),
            'extra_num' => $this->extraNum->value(),
            'reservations_rates_plans_rooms_id' => $this->reservationsRatesPlansRoomsId->value(),
            'rates_plans_rooms_id' => $this->ratesPlansRoomsId->value(),
            'channel_reservation_code' => $this->channelReservationCode->value(),
            'status_hotel' => $this->confirmationStatus->value(),
            'policies_cancellation' => $this->policiesCancellation->value(),
            'taxes_and_services' => $this->taxesAndService->value(),
            'file_amount_type_flag_id' => $this->fileAmountTypeFlagId->value(),
        ];
    }

}
