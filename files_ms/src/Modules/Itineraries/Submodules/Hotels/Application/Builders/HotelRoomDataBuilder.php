<?php

namespace Src\Modules\Itineraries\Submodules\Hotels\Application\Builders;

use Carbon\Carbon;

class HotelRoomDataBuilder
{
    public function build(array $room): array
    {        

        $hotelRooms = [];
        $sameHotelRooms = collect($room)->groupBy(function ($item) {
            return implode('_', [
                $item['hotel_id'],
                $item['room_id'],
                $item['rates_plan_id'],
                $item['nights']
            ]);
        })->toArray();
        foreach ($sameHotelRooms as $sameHotelRoom) {
            $ratePlanId = '';
            $ratePlanName = '';
            $ratePlanCode = '';
            $roomName = '';
            $roomType = '';
            $observations = '';
            $adultNum = 0;
            $childNum = 0;
            $extraNum = 0;
            $totalAmountSale = 0;
            $totalAmountBase = 0;
            $markup = 0;
            $roomUnits = $this->createHotelRoomUnitsEntity($sameHotelRoom);
            $confirmation_status = true;
            $protected_rate = false;
            $channel_reservation_code_master = '';
            foreach ($sameHotelRoom as $hotelRoom) {
                $ratePlanId = $hotelRoom['rates_plan_id'];
                $ratePlanName = $hotelRoom['rate_plan_name'];
                $ratePlanCode = $hotelRoom['rate_plan_code'];
                $roomId = $hotelRoom['room_id'];
                $roomName = $hotelRoom['room_name'];
                $roomType =
                    $hotelRoom['room_type']['translations'] ? $hotelRoom['room_type']['translations'][0]['value'] : '';
                $occupation = $hotelRoom['room_type']['occupation'];
                $channel_id = $hotelRoom['channel_id'];
                $observations = $hotelRoom['observations'];
                $adultNum += $hotelRoom['adult_num'];
                $childNum += $hotelRoom['child_num'];
                $extraNum += $hotelRoom['extra_num'];
                $totalAmountSale += $hotelRoom['total_amount'];
                $totalAmountBase += $hotelRoom['total_amount_base'];
                $markup = $hotelRoom['markup'];
                if($hotelRoom['onRequest'] ==  "0"){
                    $confirmation_status = false;
                }

                $check_in = $hotelRoom['check_in'];
                foreach($hotelRoom['rates_plans_room']['date_range_hotel'] as $dateRanges){
                    if( $check_in >= $dateRanges['date_from'] and $check_in<=$dateRanges['date_to']){
                        if($dateRanges['flag_migrate'] == "1"){
                            $protected_rate = true;
                        }
                    }
                }
                $channel_reservation_code_master = $hotelRoom['channel_reservation_code_master'];
            }
            $amountLogs = self::createFirstFileRoomAmountLogsEntity($totalAmountBase);
            $hotelRooms[] = [
                'id' => null,
                'file_itinerary_id' => null,
                'item_number' => 0,
                'total_rooms' => count($roomUnits),
                'channel_id' => $channel_id,
                'status' => true,
                'confirmation_status' => $confirmation_status,  // OK = 1, RQ = 0
                'rate_plan_id' => $ratePlanId,
                'rate_plan_name' => $ratePlanName,
                'rate_plan_code' => $ratePlanCode,
                'room_id' =>$roomId,
                'room_name' => $roomName,
                'room_type' => $roomType,
                'occupation' => $occupation,
                'additional_information' => $observations,
                'total_adults' => $adultNum,
                'total_children' => $childNum,
                'total_infants' => 0,
                'total_extra' => $extraNum,
                'currency' => 'USD',
                'amount_sale' => $totalAmountSale,
                'amount_cost' => $totalAmountBase,
                'taxed_sale' => 0,
                'taxed_cost' => 0,
                'total_amount' => $totalAmountSale,
                'markup_created' => $markup,
                'total_amount_created' => $totalAmountSale,
                'total_amount_provider' => $totalAmountBase,
                'total_amount_invoice' => $totalAmountSale,
                'total_amount_taxed' => 0,
                'total_amount_exempt' => 0,
                'taxes' => 0,
                'use_voucher' => 0,
                'use_itinerary' => 1,
                'voucher_sent' => 0,
                'voucher_number' => null,
                'use_accounting_document' => null,
                'accounting_document_sent' => null,
                'branch_number' => null,
                'document_skeleton' => false,
                'document_purchase_order' => false,
                'protected_rate' => $protected_rate,
                'view_protected_rate' => false,
                'file_room_amount_logs' => $amountLogs,
                'units' => $roomUnits,
                'file_amount_type_flag_id' => 1,
                'confirmation_code' => $channel_reservation_code_master,
                'channel_reservation_code_master' => $channel_reservation_code_master
            ];
        }
        return $hotelRooms;


    }

    private function createHotelRoomUnitsEntity(array $hotelRoomsData): array
    {
        $hotelRoomUnits = [];
        foreach ($hotelRoomsData as $hotelRoom) {
            $hotelRoomUnits[] = [
                'id' => null,
                'file_hotel_room_id' => null,
                'status' => true,  // 1= activo, 0 = cancelado
                'channel_id' => $hotelRoom['channel_id'],
                'confirmation_code' => $hotelRoom['channel_reservation_code_master'],
                'amount_sale' => $hotelRoom['total_amount'],
                'amount_cost' => $hotelRoom['total_amount_base'],
                'taxed_unit_sale' => 0,
                'taxed_unit_cost' => 0,
                'adult_num' => $hotelRoom['adult_num'],
                'child_num' => $hotelRoom['child_num'],
                'infant_num' => 0,
                'extra_num' => $hotelRoom['extra_num'],
                'reservations_rates_plans_rooms_id' => $hotelRoom['id'],
                'rates_plans_rooms_id' => $hotelRoom['rates_plans_room_id'],
                'channel_reservation_code' => $hotelRoom['channel_reservation_code'],
                'confirmation_status' => $hotelRoom['onRequest'] == "1" ? true : false,
                'policies_cancellation' => $hotelRoom['policies_cancellation'],
                'taxes_and_services' => $hotelRoom['taxes_and_services'],
                'nights' => self::createHotelRoomUnitNightsEntity($hotelRoom['reservations_hotels_calendarys']),
                'accommodations' => self::createRoomAccommodationsEntity((array) @$hotelRoom['passengers']),
                'file_amount_type_flag_id' => 1
            ];
        }
        return $hotelRoomUnits;
    }

    private function createFirstFileRoomAmountLogsEntity(float $totalAmountBase): array
    {
        $roomAmountLogs = [];
        $roomAmountLogs[] = [
            'id' => null,
            'file_amount_type_flag_id' => 1,
            'file_amount_reason_id' => 8,
            'file_hotel_room_id' => null,
            'user_id' => 1,
            'amount_previous' => 0,
            'amount' => $totalAmountBase,
        ];
        return $roomAmountLogs;
    }   
    
    private function createHotelRoomUnitNightsEntity(array $hotelRoomUnitsData): array
    {
        $hotelRoomUnitNights = [];
        foreach ($hotelRoomUnitsData as $roomUnits) {
            foreach ($roomUnits['reservation_hotel_room_date_rate'] as $roomUnit) {
                $adultSale = $roomUnit['price_adult'];
                $adultCost = $roomUnit['price_adult_base'];
                $childSale = $roomUnit['price_child'];
                $childCost = $roomUnit['price_child_base'];
                $infantSale = $roomUnit['price_infant'];
                $infantCost = $roomUnit['price_infant_base'];
                $extraSale = $roomUnit['price_extra'];
                $extraCost = $roomUnit['price_extra_base'];
                $hotelRoomUnitNights[] = [
                    'id' => null,
                    'file_hotel_room_unit_id' => null,
                    'date' => $roomUnits['date'],
                    'number' => 0,
                    'price_adult_sale' => $adultSale,
                    'price_adult_cost' => $adultCost,
                    'price_child_sale' => $childSale,
                    'price_child_cost' => $childCost,
                    'price_infant_sale' => $infantSale,
                    'price_infant_cost' => $infantCost,
                    'price_extra_sale' => $extraSale,
                    'price_extra_cost' => $extraCost,
                    'total_amount_sale' => ($adultSale + $childSale + $infantSale + $extraSale),
                    'total_amount_cost' => ($adultCost + $childCost + $infantCost + $extraCost),
                    'file_amount_type_flag_id' => 1
                ];
            }
        }

        return $hotelRoomUnitNights;
    }   
    
    private static function createRoomAccommodationsEntity(array $hotelRoomPassengerData): array
    {
        $roomAccommodations = [];
        foreach ($hotelRoomPassengerData as $passengers) {

            $roomAccommodations[] = [
                'id' => null,
                'file_hotel_room_unit_id' => null,
                'file_passenger_id' => $passengers['sequence_number'],
                'room_key' => 0
            ];
        }

        return $roomAccommodations;
    }    
}