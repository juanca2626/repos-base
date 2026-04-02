<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Model\FileTemporaryService;
use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\AddToStatement;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\AuroraReservationId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\Category;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CityInIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CityInName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CityOutName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CityOutIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryInName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryOutName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\DateOut;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\EntityObject;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryDescriptions;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryFlights;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileHotelRooms;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\MarkupCreated;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\Name;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ObjectId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryOutIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\TotalAdults;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\TotalChildren;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\TotalInfants;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneOutIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\SerialSharing;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceCode;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryInIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\DataPassengers;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ExecutiveCode;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItinearyRoomAmountLogs;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItinearyServiceAmountLogs;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryAccommodations;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryDetails;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileServices;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FilesMsParameters;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\HotelDestination;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\HotelOrigin;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\IsInOpe;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\PoliciesCancellationService;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\Profitability;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ProtectedRate;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\SentToOpe;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceCategoryId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceItinerary;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceRateId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceSubCategoryId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceSummary;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceSupplierCode;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceSupplierName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceTypeId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\Status;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneInIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\TotalAmount;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\TotalCostAmount;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ViewProtectedRate;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneInAirport;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneInId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneOutAirport;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneOutId;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;

class FileItineraryMapper
{
    public static function fromRequestToArray(
        array $services,
        array $hotels,
        array $flights,
        array $passengers,
        ?int $file_id = null,
        ?int $file_exist = null
    ): array {

        $serviceEntities = [];
        foreach ($services as $service) {
            $serviceEntities[] = self::createServiceEntity($service, $file_id, $file_exist);
        }
        foreach ($hotels as $hotel) {

            $hotel = self::validatePassengerRooms($hotel, $passengers);
            $extructure  = self::createHotelEntity($hotel, $file_id, $file_exist);
            $serviceEntities[] = $extructure;
        }
        foreach ($flights as $flight) {
            $flight['object_id'] = 0;
            $serviceEntities[] = self::createFlightEntity($flight, $file_id, $file_exist);
        }
//  dd("serevices", $serviceEntities);
        return $serviceEntities;
    }

    public static function validateFlightFields(Request $request, $file)
    {
        $itinerary = $request->toArray();

        $code_flight = isset($itinerary['code_flight']) ? $itinerary['code_flight'] : '';
        $origin = isset($itinerary['origin']) ? $itinerary['origin'] : '';
        $origin_city = isset($itinerary['origin_city']) ? $itinerary['origin_city'] : '';
        $origin_code_country = isset($itinerary['origin_code_country']) ? $itinerary['origin_code_country'] : '';
        $origin_country = isset($itinerary['origin_country']) ? $itinerary['origin_country'] : '';

        $destiny = isset($itinerary['destiny']) ? $itinerary['destiny'] : '';
        $destiny_city = isset($itinerary['destiny_city']) ? $itinerary['destiny_city'] : '';
        $destiny_code_country = isset($itinerary['destiny_code_country']) ? $itinerary['destiny_code_country'] : '';
        $destiny_country = isset($itinerary['destiny_country']) ? $itinerary['destiny_country'] : '';
        $date = isset($itinerary['date']) ? $itinerary['date'] : '';

        $adult_num = isset($itinerary['adult_num']) ? $itinerary['adult_num'] : 0;
        $child_num = isset($itinerary['child_num']) ? $itinerary['child_num'] : 0;
        $totalPax = $adult_num + $child_num;

        if($code_flight == '' or $origin == '' or $origin_city == '' or $origin_code_country == '' or $origin_country == '' or $destiny == '' or $destiny_city == '' or $destiny_code_country == '' or $destiny_country == '' or $date == '' ){
            throw new \DomainException("The code_flight,origin,destiny and date are mandatory");
        }

        if($totalPax<=0){
            throw new \DomainException("The number of passengers cannot be zero");
        }

        $adult_num_files = $file['adults'];
        $child_num_files = $file['children'];
        $totalPaxFiles = $adult_num_files + $child_num_files;

        if($totalPaxFiles < $totalPax){
            throw new \DomainException("The number of passengers admitted is greater than that allowed in the file");
        }

    }

    public static function validateServiceMaskFields(Request $request, $file)
    {
        $itinerary = $request->toArray();

        $code_flight = isset($itinerary['code_flight']) ? $itinerary['code_flight'] : '';
        $origin = isset($itinerary['origin']) ? $itinerary['origin'] : '';
        $destiny = isset($itinerary['destiny']) ? $itinerary['destiny'] : '';
        $date = isset($itinerary['date']) ? $itinerary['date'] : '';

        $adult_num = isset($itinerary['adult_num']) ? $itinerary['adult_num'] : 0;
        $child_num = isset($itinerary['child_num']) ? $itinerary['child_num'] : 0;
        $totalPax = $adult_num + $child_num;

        if($code_flight == '' or $origin == '' or $destiny == '' or $date == '' ){
            throw new \DomainException("The code_flight,origin,destiny and date are mandatory");
        }

        if($totalPax<=0){
            throw new \DomainException("The number of passengers cannot be zero");
        }

        $adult_num_files = $file['adults'];
        $child_num_files = $file['children'];
        $totalPaxFiles = $adult_num_files + $child_num_files;

        if($totalPaxFiles < $totalPax){
            throw new \DomainException("The number of passengers admitted is greater than that allowed in the file");
        }

    }

    public static function fromRequestCreate(Request $request, array $file): FileItinerary
    {

        $itinerary = $request->toArray();
       //dd($itinerary);
        if(isset($itinerary['entity']) and $itinerary['entity'] == 'flight'){

            self::validateFlightFields($request, $file);

            $itinerary['object_id'] = 0;
            $itinerary['adult_num'] = isset($itinerary['adult_num']) ? $itinerary['adult_num'] : $file['adults'];
            $itinerary['child_num'] = isset($itinerary['child_num']) ? $itinerary['child_num'] : $file['children'];
            $itinerary['inf_num'] = isset($itinerary['inf_num']) ? $itinerary['inf_num'] : $file['infants'];
            $itinerary = self::createFlightEntity($itinerary, $file['id'], null);

        }elseif(isset($itinerary['entity']) and $itinerary['entity'] == 'service-mask'){
                // self::validateServiceMaskFields($request, $file);
                // $itinerary['service_mask_supplier_code'] = $itinerary['service_supplier_code'];
                // $itinerary['service_mask_supplier_name'] = $itinerary['service_supplier_name'];
                $itinerary['object_id'] = $itinerary['service_id'];
                $itinerary['object_code'] = $itinerary['service_code'];
                $itinerary['name'] = $itinerary['name'];
                $itinerary['adult_num'] = isset($itinerary['adult_num']) ? $itinerary['adult_num'] : $file['adults'];
                $itinerary['child_num'] = isset($itinerary['child_num']) ? $itinerary['child_num'] : $file['children'];
                $itinerary['inf_num'] = isset($itinerary['inf_num']) ? $itinerary['inf_num'] : $file['infants'];
                $itinerary = self::createServiceMaskEntity($itinerary, $file['id']);

        }elseif(isset($itinerary['entity']) and $itinerary['entity'] == 'service-temporary'){

            // self::validateServiceTemporaryFields($request, $file);
                // $itinerary['service_mask_supplier_name'] = $itinerary['service_supplier_name'];
                $itinerary['object_id'] = $itinerary['service_id'];
                $itinerary['object_code'] = $itinerary['service_code'];
                $itinerary['name'] = $itinerary['name'];
                $itinerary['adult_num'] = isset($itinerary['adult_num']) ? $itinerary['adult_num'] : $file['adults'];
                $itinerary['child_num'] = isset($itinerary['child_num']) ? $itinerary['child_num'] : $file['children'];
                $itinerary['inf_num'] = isset($itinerary['inf_num']) ? $itinerary['inf_num'] : $file['infants'];

                $itinerary = self::createServiceTemporaryEntity($itinerary, $file['id']);

        }elseif(isset($itinerary['entity']) and $itinerary['entity'] == 'service-zero'){
             //dd($itinerary);}

                 $itinerary['object_id'] = $itinerary['service_id'];
                $itinerary['object_code'] = $itinerary['service_code'];
                $itinerary['name'] = $itinerary['name'];
                $itinerary['adult_num'] = isset($itinerary['adult_num']) ? $itinerary['adult_num'] : $file['adults'];
                $itinerary['child_num'] = isset($itinerary['child_num']) ? $itinerary['child_num'] : $file['children'];
                $itinerary['inf_num'] = isset($itinerary['inf_num']) ? $itinerary['inf_num'] : $file['infants'];
                $itinerary = self::createServiceMaskEntity($itinerary, $file['id']);

        }
        else{
            throw new \DomainException("The entity field must be flight/service-mask/service-temporary/service-zero");
        }

        return FileItineraryMapper::fromArray($itinerary);

    }

    public static function fromRequestItineraryTemporaryCreate($params, FileTemporaryService $fileTemporaryService): FileItinerary
    {

        $fileTemporaryService = $fileTemporaryService->toArray();
        $fileTemporaryService['id'] = null;
        $fileTemporaryService['protected_rate'] = 0;
        $fileTemporaryService['view_protected_rate'] = false;
        $fileTemporaryService['add_to_statement'] = 0;


        $details = [];
        foreach($fileTemporaryService['details'] as $detail){

            $detail =  $detail->toArray();
            $detail['id'] = null;
            $detail['file_itinerary_id'] = null;
            unset($detail['file_temporary_service_id']);
            array_push($details, $detail);
        }

        $serviceEntities = [];
        foreach ($fileTemporaryService['services'] as $serv) {
            $serv = $serv->toArray();
            $serv['id'] = null;
            $serv['file_itinerary_id'] = null;
            $serv['code'] = $serv['code_ifx'];
            $serv['status'] = 1;
            $serv['is_in_ope'] = 0;
            $serv['sent_to_ope'] = 0;
            $serv['file_service_amount'] =  [
                'id' => NULL,
                'file_amount_type_flag_id' => 1,
                'file_amount_reason_id' => 8,
                'file_service_id' => 0,
                'user_id' => 1,
                'amount_previous' => 0,
                'amount' =>   $serv['amount_cost']
            ];
            unset($detail['file_temporary_service_id']);
            unset($serv['code_ifx']);
            foreach($serv['compositions'] as $index => $composition){
                // dd($composition->toArray());
                $composition = $composition->toArray();
                $composition['id'] = null;
                $composition['file_service_id'] = null;
                $composition['is_in_ope'] = 0;
                $composition['sent_to_ope'] = 0;
                unset($composition['file_temporary_master_service_id']);

                foreach($composition['units'] as $index2 => $unit){
                    $unit = $unit->toArray();
                    $unit['id'] = null;
                    $unit['file_service_composition_id'] = null;
                    $unit['status'] = 1;
                    unset($unit['file_temporary_service_composition_id']);

                    $composition['units'][$index2] = $unit;
                }

                $serv['compositions'][$index] = $composition;
            }

            array_push($serviceEntities,$serv);
        }

        $fileTemporaryService['services'] = $serviceEntities;
        $fileTemporaryService['details'] = $details;

        // dd($fileTemporaryService);

        return FileItineraryMapper::fromArray($fileTemporaryService);

    }

    public static function fromRequestUpdate(Request $request, array $file, FileItinerary $fileItinerary): FileItinerary
    {
        $itinerary = $request->toArray();

        if(isset($itinerary['entity']) and $itinerary['entity'] == 'flight'){

            self::validateFlightFields($request, $file);

            $itinerary['object_id'] = $fileItinerary->objectId->value();
            $itinerary['file_id'] = $fileItinerary->fileId->value();
            $itinerary['status'] = $fileItinerary->status->value();
            $itinerary['is_in_ope'] = $fileItinerary->isInOpe->value();
            $itinerary['sent_to_ope'] = $fileItinerary->sentToOpe->value();
            $itinerary['hotel_origin'] = $fileItinerary->hotelOrigin->value();
            $itinerary['hotel_destination'] = $fileItinerary->hotelDestination->value();
            $itinerary['adult_num'] = isset($itinerary['adult_num']) ? $itinerary['adult_num'] : $fileItinerary->totalAdults->value();
            $itinerary['child_num'] = isset($itinerary['child_num']) ? $itinerary['child_num'] : $fileItinerary->totalChildren->value();
            $itinerary['inf_num'] = isset($itinerary['inf_num']) ? $itinerary['inf_num'] : $fileItinerary->totalInfants->value();

            $itinerary = self::updateFlightEntity($itinerary, $fileItinerary->id, null);
        }else{
            throw new \DomainException("The entity field must be flight");
        }

        return FileItineraryMapper::fromArray($itinerary);

    }

    private static function validatePassengerRooms(array $hotel, array $passengers): array
    {
        $passengerNull = false;
        foreach($hotel['reservations_hotel_rooms'] as $id => $room){
            $hotel['reservations_hotel_rooms'][$id]['occupation'] =  $room['room_type']['occupation'];
            if(!isset($room['passengers']) or $room['passengers'] == null ){
                $passengerNull = true;
            }
        }

        $passengerCollets = collect($passengers)->sortBy('sequence_number');
        $rooms = collect($hotel['reservations_hotel_rooms'])->sortBy('occupation');
        if($passengerNull == true){

            $passengerItems = [];
            foreach($rooms as $key => $room){

                $adult_num = $room['adult_num'];
                $child_num = $room['child_num'];
                $passengerAdd = [];

                for($i=0; $i<$adult_num; $i++){

                    foreach($passengerCollets as $item => $passenger){
                        if($passenger['type'] == 'ADL' and !in_array($item, $passengerItems)){
                            array_push($passengerAdd, [
                                "reservation_passenger_id" => $passenger['id'],
                                "sequence_number" => $passenger['sequence_number']
                            ]);
                            array_push($passengerItems, $item);
                            break;
                        }
                    }

                }

                for($i=0; $i<$child_num; $i++){

                    foreach($passengerCollets as $item => $passenger){
                        if($passenger['type'] == 'CHD' and !in_array($item, $passengerItems)){
                            array_push($passengerAdd, [
                                "reservation_passenger_id" => $passenger['id'],
                                "sequence_number" => $passenger['sequence_number']
                            ]);
                            array_push($passengerItems, $item);
                            break;
                        }
                    }

                }

               $hotel['reservations_hotel_rooms'][$key]['passengers'] = $passengerAdd;

            }

        }
        return $hotel;
    }
    private static function createServiceEntity(array $service, ?int $file_id, ?bool $file_exist): array
    {

        $city_in_iso = isset($service['service']['service_origin'][0]['city']) ? $service['service']['service_origin'][0]['city']['iso'] : NULL;
        $city_in_translations = isset($service['service']['service_origin'][0]['city']) ? $service['service']['service_origin'][0]['city']['translations'][0]['value'] : NULL;
        if(!$city_in_iso){
            $city_in_iso = $service['service']['service_origin'][0]['state']['iso'];
            if(!$city_in_iso){
                $city_in_iso = "LIM";
            }
        }

        $city_out_iso = isset($service['service']['service_destination'][0]['city']) ? $service['service']['service_destination'][0]['city']['iso'] : NULL;
        $city_out_translations = isset($service['service']['service_destination'][0]['city']) ? $service['service']['service_destination'][0]['city']['translations'][0]['value'] : NULL;
        if(!$city_out_iso){
            $city_out_iso = $service['service']['service_destination'][0]['state']['iso'];
            if(!$city_out_iso){
                $city_out_iso = "LIM";
            }
        }

        $protected_rate = isset($service['reservations_service_rates_plans']['service_rates_plans']) ? $service['reservations_service_rates_plans']['service_rates_plans']['flag_migrate'] : 0 ;

        $service_translations = collect($service['service']['service_translations'])->first(function($item) {
            return $item['language_id'] == 1;
        });


        $accommodations = [];
        if(isset($service['passengers'])){
            foreach ($service['passengers'] as $passenger) {
                $accommodations[] = [
                    'id' => null,
                    'file_itinerary_id' => null,
                    'file_passenger_id' => $passenger['sequence_number']
                ];
            }
        }

        $add_to_statement = true;
        if($file_exist !== null){
            $add_to_statement = false;
        }

        return [
            'id' => null,
            'file_id' => $file_id,
            'entity' => 'service',
            'object_id' => $service['service_id'],
            'name' => $service['service_name'],
            'category' => $service['service']['service_type']['translations'][0]['value'],
            'object_code' => $service['service_code'],
            'country_in_iso' => $service['service']['service_origin'][0]['country']['iso'],
            'country_in_name' => $service['service']['service_origin'][0]['country']['translations'][0]['value'],
            'city_in_iso' => $city_in_iso,
            'city_in_name' => $city_in_translations,
            'zone_in_iso' => null,
            'zone_in_id' => isset($service['service']['service_origin'][0]['zone']) ? $service['service']['service_origin'][0]['zone']['id'] : null,
            'zone_in_airport' => (isset($service['service']['service_origin'][0]['zone']['in_airport']) and $service['service']['service_origin'][0]['zone']['in_airport'] != null) ? $service['service']['service_origin'][0]['zone']['in_airport'] : 0,
            'country_out_iso' => $service['service']['service_destination'][0]['country']['iso'],
            'country_out_name' => $service['service']['service_destination'][0]['country']['translations'][0]['value'],
            'city_out_iso' => $city_out_iso,
            'city_out_name' => $city_out_translations,
            'zone_out_iso' => null,
            'zone_out_id' => isset($service['service']['service_destination'][0]['zone']) ? $service['service']['service_destination'][0]['zone']['id'] : null,
            'zone_out_airport' => (isset($service['service']['service_destination'][0]['zone']['in_airport']) and $service['service']['service_destination'][0]['zone']['in_airport'] != null) ? $service['service']['service_destination'][0]['zone']['in_airport'] : 0,
            'start_time' => $service['time'],
            'departure_time' => null,
            'date_in' => $service['date'],
            'date_out' => $service['date'],
            'total_adults' => $service['adult_num'],
            'total_children' => $service['child_num'],
            'total_infants' => $service['infant_num'],
            'markup_created' => $service['markup'],
            'total_amount' => $service['total_amount'],
            'total_cost_amount' => 0,
            'profitability' => 0,
            'serial_sharing' => 0,
            'status' => true,
            'confirmation_status' => true,
            'policies_cancellation_service' => count($service['reservations_service_rates_plans'])>0 ? json_encode($service['reservations_service_rates_plans'][0]['reservation_service_cancel_policies']) : '',
            'data_passengers' => isset($service['passengers']) ? json_encode($service['passengers']) : NULL,
            'service_rate_id' => count($service['reservations_service_rates_plans'])>0 ? $service['reservations_service_rates_plans'][0]['service_rate_id'] : NULL,
            'is_in_ope' => 0,
            'sent_to_ope' =>0,
            'protected_rate' => $protected_rate,
            'view_protected_rate' => false,
            'hotel_origin' => isset($service['origin']) ? true : false,
            'hotel_destination' => isset($service['destination']) ? true : false,
            'service_category_id' => isset($service['service']['service_sub_category']) ? $service['service']['service_sub_category']['service_category_id'] : null,
            'service_sub_category_id' => isset($service['service']['service_sub_category_id']) ? $service['service']['service_sub_category_id'] : null,
            'service_type_id' => isset($service['service']['service_type_id']) ? $service['service']['service_type_id'] : null,
            'service_summary' => isset($service_translations) ? strip_tags($service_translations['summary']) : null,
            'service_itinerary' => isset($service_translations) ? strip_tags($service_translations['itinerary']) : null,
            'rooms' => [],
            'accommodations' => $accommodations,
            'add_to_statement' => $add_to_statement,
            'aurora_reservation_id' => $service['id'],
            'files_ms_parameters' => isset($service['files_ms_parameters']) ? $service['files_ms_parameters'] : null
        ];
    }

    private static function createHotelEntity(array $hotel, ?int $file_id, ?bool $file_exist): array
    {

        $confirmation_status = true;
        foreach($hotel['reservations_hotel_rooms'] as $hotelRooms){
            if($hotelRooms['onRequest'] === 0){
                $confirmation_status = false;
            }

        }

        $city_in_iso = $hotel['hotel']['city']['iso'];
        if(!$city_in_iso){
            $city_in_iso = $hotel['hotel']['state']['iso'];
            if(!$city_in_iso){
                $city_in_iso = "LIM";
            }
        }

        $protected_rate = false;
        $rooms = self::createHotelRoomsEntity($hotel['reservations_hotel_rooms']);
        foreach($rooms as $room){
            if($room['protected_rate']){
                $protected_rate = true;
            }
        }

        $category = "";
        if(isset($hotel['hotel']['hoteltypeclass'])){
            if(is_array($hotel['hotel']['hoteltypeclass']) and count($hotel['hotel']['hoteltypeclass'])>0){
                $category = $hotel['hotel']['hoteltypeclass'][0]['typeclass']['translations'][0]['value'];
            }
        }

        $add_to_statement = true;
        if($file_exist !== null){
            $add_to_statement = false;
        }

        return [
            'id' => null,
            'file_id' => $file_id,
            'entity' => 'hotel',
            'object_id' => $hotel['hotel_id'],
            'name' => $hotel['hotel_name'],
            'category' => $category,
            'object_code' => $hotel['hotel_code'],
            'country_in_iso' => $hotel['hotel']['country']['iso'],
            'country_in_name' => $hotel['hotel']['country']['translations'][0]['value'],
            'city_in_iso' => $city_in_iso,
            'city_in_name' => $hotel['hotel']['city']['translations'][0]['value'],
            'zone_in_iso' => null,
            'country_out_iso' => $hotel['hotel']['country']['iso'],
            'country_out_name' => $hotel['hotel']['country']['translations'][0]['value'],
            'city_out_iso' => $city_in_iso,
            'city_out_name' => $hotel['hotel']['city']['translations'][0]['value'],
            'zone_out_iso' => null,
            'start_time' => $hotel['check_in_time'],
            'departure_time' => $hotel['check_out_time'],
            'city_name' => $hotel['hotel']['city']['translations'][0]['value'] ?? null,
            'country_name' => $hotel['hotel']['country']['translations'][0]['value'] ?? null,
            'date_in' => $hotel['check_in'],
            'date_out' => $hotel['check_out'],
            'total_adults' => 0,
            'total_children' => 0,
            'total_infants' => 0,
            'markup_created' => $hotel['reservations_hotel_rooms'][0]['markup'],
            'total_amount' => $hotel['total_amount'],
            'total_cost_amount' => 0,
            'profitability' => 0,
            'serial_sharing' => 0,
            'executive_code' => $hotel['executive_code'],
            'status' => true,
            'confirmation_status' => $confirmation_status,
            'protected_rate' => $protected_rate,
            'view_protected_rate' => false,
            'is_in_ope' => 0,
            'sent_to_ope' => 0,
            'hotel_origin' => NULL,
            'hotel_destination' => NULL,
            'rooms' => $rooms,
            'add_to_statement' => $add_to_statement,
            'aurora_reservation_id' => $hotel['id'],
            'files_ms_parameters' => isset($hotel['files_ms_parameters']) ? $hotel['files_ms_parameters'] : null

        ];
    }

    private static function createHotelRoomsEntity(array $hotelRoomsData): array
    {
        $hotelRooms = [];
        $sameHotelRooms = collect($hotelRoomsData)->groupBy(function ($item) {
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
            $roomUnits = self::createHotelRoomUnitsEntity($sameHotelRoom);
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

    private static function createFirstFileRoomAmountLogsEntity(float $totalAmountBase): array
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

    private static function createHotelRoomUnitsEntity(array $hotelRoomsData): array
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

    private static function createHotelRoomUnitNightsEntity(array $hotelRoomUnitsData): array
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

    // el vuelo no tiene importe no afecta al statement
    private static function createFlightEntity(array $flight, ?int $file_id, ?bool $file_exist): array
    {
        $city_in_iso = $flight['origin'];
        $city_in_name = isset($flight['origin_city']) ? $flight['origin_city'] : NULL;
        $country_in_iso = isset($flight['origin_code_country']) ? $flight['origin_code_country']: NULL;
        $country_in_name = isset($flight['origin_country']) ? $flight['origin_country']: NULL;

        // if(!$city_in_iso){
        //     $city_in_iso = "LIM";
        // }

        $city_out_iso = $flight['destiny'];
        $city_out_name = isset($flight['destiny_city']) ?  $flight['destiny_city'] : NULL;
        $country_out_iso = isset($flight['destiny_code_country']) ?  $flight['destiny_code_country'] : NULL;
        $country_out_name = isset($flight['destiny_country']) ?  $flight['destiny_country'] : NULL;
        // if(!$city_out_iso){
        //     $city_out_iso = "LIM";
        // }

        $add_to_statement = true;
        // if($file_exist !== null){
        //     $add_to_statement = false;
        // }

        return [
            'id' => null,
            'file_id' => $file_id,
            'entity' => 'flight',
            'object_id' => $flight['object_id'],
            'name' => $flight['code_flight'],
            'category' => null,
            'object_code' => $flight['code_flight'],
            'country_in_iso' => $country_in_iso,
            'country_in_name' => $country_in_name,
            'city_in_iso' => $city_in_iso,
            'city_in_name' => $city_in_name,
            'zone_in_iso' => null,
            'country_out_iso' => $country_out_iso,
            'country_out_name' => $country_out_name,
            'city_out_iso' => $city_out_iso,
            'city_out_name' => $city_out_name,
            'zone_out_iso' => null,
            'start_time' => null,
            'departure_time' => null,
            'date_in' => $flight['date'],
            'date_out' => $flight['date'],
            'total_adults' => $flight['adult_num'],
            'total_children' => $flight['child_num'],
            'total_infants' => $flight['inf_num'],
            'markup_created' => 0,
            'total_amount' => 0,
            'total_cost_amount' => 0,
            'profitability' => 0,
            'serial_sharing' => 0,
            'status' => true,
            'confirmation_status' => true,
            'protected_rate' => 0,
            'view_protected_rate' => false,
            'serial_sharing' => 0,
            'is_in_ope' => 0,
            'sent_to_ope' => 0,
            'hotel_origin' => NULL,
            'hotel_destination' => NULL,
            'rooms' => [],
            'add_to_statement' => $add_to_statement,
            'aurora_reservation_id' => isset($flight['id']) ? $flight['id'] : NULL,
            'files_ms_parameters' => null
        ];
    }

    // el vuelo no tiene importe no afecta al statement
    private static function updateFlightEntity(array $flight, int $file_itinerary_id, ?bool $file_exist): array
    {

        $add_to_statement = true;
        // if($file_exist !== null){
        //     $add_to_statement = false;
        // }


        return [
            'id' => $file_itinerary_id,
            'file_id' => $flight['file_id'],
            'entity' => 'flight',
            'object_id' => $flight['object_id'],
            'name' => $flight['code_flight'],
            'category' => null,
            'object_code' => $flight['code_flight'],
            'country_in_iso' => $flight['origin_code_country'],
            'country_in_name' => $flight['origin_country'],
            'city_in_iso' => $flight['origin'],
            'city_in_name' => $flight['origin_city'],
            'zone_in_iso' => null,
            'country_out_iso' => $flight['destiny_code_country'],
            'country_out_name' => $flight['destiny_country'],
            'city_out_iso' => $flight['destiny'],
            'city_out_name' => $flight['destiny_city'],
            'zone_out_iso' => null,
            'start_time' => null,
            'departure_time' => null,
            'date_in' => $flight['date'],
            'date_out' => $flight['date'],
            'total_adults' => $flight['adult_num'],
            'total_children' => $flight['child_num'],
            'total_infants' => $flight['inf_num'],
            'markup_created' => 0,
            'total_amount' => 0,
            'total_cost_amount' => 0,
            'profitability' => 0,
            'serial_sharing' => 0,
            'status' => true,
            'confirmation_status' => true,
            'protected_rate' => 0,
            'view_protected_rate' => false,
            'serial_sharing' => 0,
            'is_in_ope' =>  $flight['is_in_ope'],
            'sent_to_ope' => $flight['sent_to_ope'],
            'hotel_origin' => $flight['hotel_origin'],
            'hotel_destination' => $flight['hotel_destination'],
            'rooms' => [],
            'add_to_statement' => $add_to_statement

        ];

    }

    private static function createServiceMaskEntity(array $service, ?int $file_id): array
    {

        $details = [];
        foreach($service['details'] as $detail){
            $detail['id'] = null;
            $detail['file_itinerary_id'] = null;
            array_push($details, $detail);
        }

        $accommodations = [];
        foreach ($service['accommodations'] as $passengers) {
            $accommodations[] = [
                'id' => null,
                'file_itinerary_id' => null,
                'file_passenger_id' => $passengers
            ];
        }


        return [
            'id' => null,
            'file_id' => $file_id,
            'entity' => 'service-mask',
            'object_id' => $service['object_id'],
            'name' => $service['name'],
            'category' => null,
            'object_code' => $service['object_code'],
            'country_in_iso' => null,
            'country_in_name' => null,
            'city_in_iso' => null,
            'city_in_name' => null,
            'zone_in_iso' => null,
            'country_out_iso' => null,
            'country_out_name' => null,
            'city_out_iso' => null,
            'city_out_name' => null,
            'zone_out_iso' => null,
            'start_time' => null,
            'departure_time' => null,
            'date_in' => date('Y-m-d'),
            'date_out' => date('Y-m-d'),
            'total_adults' => $service['adult_num'],
            'total_children' => $service['child_num'],
            'total_infants' => $service['inf_num'],
            'markup_created' => 0,
            'total_amount' => $service['total_amount'],
            'total_cost_amount' => $service['total_cost_amount'],
            'profitability' => 0,
            'serial_sharing' => 0,
            'status' => true,
            'confirmation_status' => true,
            'protected_rate' => 0,
            'view_protected_rate' => false,
            'serial_sharing' => 0,
            'is_in_ope' => 0,
            'sent_to_ope' => 0,
            'hotel_origin' => NULL,
            'hotel_destination' => NULL,
            'service_mask_supplier_code' => $service['service_supplier_code'],
            'service_mask_supplier_name' => $service['service_supplier_name'],
            'rooms' => [],
            'details'=> $details,
            'accommodations'=> $accommodations,
            'add_to_statement' => false,
            'aurora_reservation_id' => null,
            'files_ms_parameters' => null

        ];
    }

    private static function createServiceTemporaryEntity(array $service, ?int $file_id): array
    {

        $details = [];
        foreach($service['details'] as $detail){
            $detail['id'] = null;
            $detail['file_itinerary_id'] = null;
            array_push($details, $detail);
        }

        $serviceEntities = [];
        foreach ($service['services'] as $serv) {
            $serv['status'] = 1;
            $serv['date_out'] = $serv['date_in'];
            $serv['is_in_ope'] = 0;
            $serv['sent_to_ope'] = 0;
            $serv['file_service_amount'] =  [
                'id' => NULL,
                'file_amount_type_flag_id' => 1,
                'file_amount_reason_id' => 8,
                'file_service_id' => 0,
                'user_id' => 1,
                'amount_previous' => 0,
                'amount' =>  $serv['amount_cost']
            ];

            foreach($serv['compositions'] as $index => $composition){
                $composition['is_in_ope'] = 0;
                $composition['sent_to_ope'] = 0;

                foreach($composition['units'] as $index2 => $unit){
                    $unit['status'] = 1;
                    $unit['units'][$index2] = $unit;
                    $composition['units'][$index2] = $unit;
                }

                $serv['compositions'][$index] = $composition;
            }

            array_push($serviceEntities,$serv);
        }

        return [
            'id' => null,
            'file_id' => $file_id,
            'entity' => 'service-temporary',
            'object_id' => $service['object_id'],
            'name' => $service['name'],
            'category' => $service['category'],
            'object_code' => $service['object_code'],
            'country_in_iso' => $service['country_in_iso'],
            'country_in_name' => $service['country_in_name'],
            'city_in_iso' => $service['city_in_iso'],
            'city_in_name' => $service['city_in_name'],
            'zone_in_iso' => $service['zone_in_iso'],
            'country_out_iso' => $service['country_out_iso'],
            'country_out_name' => $service['country_out_name'],
            'city_out_iso' => $service['city_out_iso'],
            'city_out_name' => $service['city_out_name'],
            'zone_out_iso' => $service['zone_out_iso'],
            'start_time' => $service['start_time'],
            'departure_time' => $service['departure_time'],
            'date_in' => date('Y-m-d'),
            'date_out' => date('Y-m-d'),
            'total_adults' => $service['adult_num'],
            'total_children' => $service['child_num'],
            'total_infants' => $service['inf_num'],
            'markup_created' => $service['markup_created'],
            'total_amount' => $service['total_amount'],
            'total_cost_amount' => $service['total_cost_amount'],
            'profitability' => 0,
            'serial_sharing' => 0,
            'status' => true,
            'confirmation_status' => true,
            'serial_sharing' => 0,
            'is_in_ope' => 0,
            'sent_to_ope' => 0,
            'hotel_origin' => NULL,
            'hotel_destination' => NULL,
            'service_mask_supplier_code' => $service['service_supplier_code'],
            'service_mask_supplier_name' => $service['service_supplier_name'],
            'rooms' => [],
            'services' => $serviceEntities,
            'details'=> $details,
            'add_to_statement' => false,
            'aurora_reservation_id' => null,
            'files_ms_parameters' => null
        ];
    }

    public static function fromArray(array $itinerary): FileItinerary
    {

        $fileItineraryEloquentModel = new FileItineraryEloquentModel($itinerary);
        $fileItineraryEloquentModel->id = $itinerary['id'] ?? null;
        $fileItineraryEloquentModel->rooms = collect();
        $fileItineraryEloquentModel->services = collect();
        $fileItineraryEloquentModel->flights = collect();
        $fileItineraryEloquentModel->details = collect();
        $fileItineraryEloquentModel->accommodations = collect();

        if($itinerary['entity'] == 'hotel' && isset($itinerary['rooms'])) {
            foreach($itinerary['rooms'] as $room) {
                $fileItineraryEloquentModel->rooms->add($room);
            }
        }

        if($itinerary['entity'] == 'service' && isset($itinerary['services'])) {

            foreach($itinerary['services'] as $service) {
                $fileItineraryEloquentModel->services->add($service);
            }

            if(isset($itinerary['accommodations'])){
                foreach($itinerary['accommodations'] as $accommodation) {
                    $fileItineraryEloquentModel->accommodations->add($accommodation);
                }
            }

        }else{
            if($itinerary['entity'] == 'service' && !isset($itinerary['services'])) {
                if(isset($itinerary['accommodations'])){
                    foreach($itinerary['accommodations'] as $accommodation) {
                        $fileItineraryEloquentModel->accommodations->add($accommodation);
                    }
                }
            }

        }

        if($itinerary['entity'] == 'flight' && isset($itinerary['flights'])) {
            foreach($itinerary['flights'] as $flight) {
                $fileItineraryEloquentModel->flights->add($flight);
            }
        }

        if($itinerary['entity'] == 'service-mask' || $itinerary['entity'] == 'service-temporary' || $itinerary['entity'] == 'service-zero') {

            if(isset($itinerary['details'])){
                foreach($itinerary['details'] as $detail) {
                    $fileItineraryEloquentModel->details->add($detail);
                }
            }

            if(isset($itinerary['accommodations'])){
                foreach($itinerary['accommodations'] as $accommodation) {
                    $fileItineraryEloquentModel->accommodations->add($accommodation);
                }
            }

            if(isset($itinerary['services'])){
                foreach($itinerary['services'] as $service) {
                    $fileItineraryEloquentModel->services->add($service);
                }
            }
        }


        return self::fromEloquent($fileItineraryEloquentModel);
    }

    public static function fromEloquent(FileItineraryEloquentModel $fileItineraryEloquent): FileItinerary
    {
        // dd($fileItineraryEloquent->details);
        $details = array_map(function ($detail) {
            return FileItineraryDetailMapper::fromArray($detail);
        }, $fileItineraryEloquent->details?->toArray() ?? []);

        $accommodations = array_map(function ($accommodation) {
            return FileItineraryAccommodationMapper::fromArray($accommodation);
        }, $fileItineraryEloquent->accommodations?->toArray() ?? []);

        $flights = array_map(function ($itineraries) {
            return FileItineraryFlightMapper::fromArray($itineraries);
        }, $fileItineraryEloquent->flights?->toArray() ?? []);

        $descriptions = array_map(function ($descriptions) {
            return FileItineraryDescriptionMapper::fromArray($descriptions);
        }, $fileItineraryEloquent->descriptions?->toArray() ?? []);

        $rooms = array_map(function ($rooms) {
            return FileHotelRoomMapper::fromArray($rooms);
        }, $fileItineraryEloquent->rooms?->toArray() ?? []);

        $services = array_map(function ($services) {
            return FileServiceMapper::fromArray($services);
        }, $fileItineraryEloquent->services?->toArray() ?? []);

        $serviceAmountLogs = array_map(function ($amount_logs) {
            return FileItineraryServiceAmountLogMapper::fromArray($amount_logs);
        }, $fileItineraryEloquent->service_amount_logs?->toArray() ?? []);

        $roomAmountLogs = array_map(function ($amount_logs) {
            return FileItineraryRoomAmountLogMapper::fromArray($amount_logs);
        }, $fileItineraryEloquent->room_amount_logs?->toArray() ?? []);

        return new FileItinerary(
            id: $fileItineraryEloquent->id,
            fileId: new FileId($fileItineraryEloquent->file_id),
            entity: new EntityObject($fileItineraryEloquent->entity),
            objectId: new ObjectId($fileItineraryEloquent->object_id),
            name: new Name($fileItineraryEloquent->name),
            category: new Category($fileItineraryEloquent->category),
            serviceCode: new ServiceCode($fileItineraryEloquent->object_code),
            countryInIso: new CountryInIso($fileItineraryEloquent->country_in_iso),
            countryInName: new CountryInName($fileItineraryEloquent->country_in_name),
            cityInIso: new CityInIso($fileItineraryEloquent->city_in_iso),
            cityInName: new CityInName($fileItineraryEloquent->city_in_name),
            zoneInIso: new ZoneInIso($fileItineraryEloquent->zone_in_iso),
            zoneInId: new ZoneInId($fileItineraryEloquent->zone_in_id),
            zoneInAirport: new ZoneInAirport($fileItineraryEloquent->zone_in_airport),
            countryOutIso: new CountryOutIso($fileItineraryEloquent->country_out_iso),
            countryOutName: new CountryOutName($fileItineraryEloquent->country_out_name),
            cityOutIso: new CityOutIso($fileItineraryEloquent->city_out_iso),
            cityOutName: new CityOutName($fileItineraryEloquent->city_out_name),
            zoneOutIso: new ZoneOutIso($fileItineraryEloquent->zone_out_iso),
            zoneOutId: new ZoneOutId($fileItineraryEloquent->zone_out_id),
            zoneOutAirport: new ZoneOutAirport($fileItineraryEloquent->zone_out_airport),
            startTime: new StartTime($fileItineraryEloquent->start_time),
            departureTime: new DepartureTime($fileItineraryEloquent->departure_time),
            dateIn: new DateIn($fileItineraryEloquent->date_in),
            dateOut: new DateOut($fileItineraryEloquent->date_out),
            totalAdults: new TotalAdults($fileItineraryEloquent->total_adults),
            totalChildren: new TotalChildren($fileItineraryEloquent->total_children),
            totalInfants: new TotalInfants($fileItineraryEloquent->total_infants),
            markupCreated: new MarkupCreated($fileItineraryEloquent->markup_created),
            totalAmount: new TotalAmount($fileItineraryEloquent->total_amount),
            totalCostAmount : new TotalCostAmount($fileItineraryEloquent->total_cost_amount),
            profitability : new Profitability($fileItineraryEloquent->profitability),
            serialSharing: new SerialSharing($fileItineraryEloquent->serial_sharing),
            executiveCode: new ExecutiveCode($fileItineraryEloquent->executive_code),
            status: new Status($fileItineraryEloquent->status),
            confirmationStatus: new ConfirmationStatus($fileItineraryEloquent->confirmation_status),
            createdAt: new CreatedAt($fileItineraryEloquent->created_at),
            dataPassengers: new DataPassengers($fileItineraryEloquent->data_passengers),
            flights: new FileItineraryFlights($flights),
            descriptions: new FileItineraryDescriptions($descriptions),
            policiesCancellationService: new PoliciesCancellationService($fileItineraryEloquent->policies_cancellation_service),
            serviceRateId: new ServiceRateId($fileItineraryEloquent->service_rate_id),
            isInOpe: new IsInOpe($fileItineraryEloquent->is_in_ope),
            sentToOpe: new SentToOpe($fileItineraryEloquent->sent_to_ope),
            hotelOrigin: new HotelOrigin($fileItineraryEloquent->hotel_origin),
            hotelDestination: new HotelDestination($fileItineraryEloquent->hotel_destination),
            rooms: new FileHotelRooms($rooms),
            services: new FileServices($services),
            serviceAmountLogs: new FileItinearyServiceAmountLogs($serviceAmountLogs),
            roomAmountLogs: new FileItinearyRoomAmountLogs($roomAmountLogs),
            serviceSupplierCode: new ServiceSupplierCode($fileItineraryEloquent->service_supplier_code),
            serviceSupplierName: new ServiceSupplierName($fileItineraryEloquent->service_supplier_name),
            details: new FileItineraryDetails($details),
            accommodations: new FileItineraryAccommodations($accommodations),
            protectedRate: new ProtectedRate($fileItineraryEloquent->protected_rate),
            viewProtectedRate: new ViewProtectedRate($fileItineraryEloquent->view_protected_rate),
            serviceCategoryId: new ServiceCategoryId($fileItineraryEloquent->service_category_id),
            serviceSubCategoryId: new ServiceSubCategoryId($fileItineraryEloquent->service_sub_category_id),
            serviceTypeId: new ServiceTypeId($fileItineraryEloquent->service_type_id),
            serviceSummary: new ServiceSummary($fileItineraryEloquent->service_summary),
            serviceItinerary: new ServiceItinerary($fileItineraryEloquent->service_itinerary),
            addToStatement: new AddToStatement($fileItineraryEloquent->add_to_statement),
            auroraReservationId: new AuroraReservationId($fileItineraryEloquent->aurora_reservation_id),
            filesMsParameters: new FilesMsParameters($fileItineraryEloquent->files_ms_parameters)
            // flights: (count($flights)) ? new FileFlights($flights) : [],
        );
    }

    public static function toEloquent(FileItinerary $fileItinerary): FileItineraryEloquentModel
    {
        $fileItineraryEloquent = new FileItineraryEloquentModel();
        if ($fileItinerary->id) {
            $fileItineraryEloquent = FileItineraryEloquentModel::query()->findOrFail($fileItinerary->id);
        }
        $fileItineraryEloquent->file_id = $fileItinerary->fileId->value();
        $fileItineraryEloquent->entity = $fileItinerary->entity->value();
        $fileItineraryEloquent->object_id = $fileItinerary->objectId->value();
        $fileItineraryEloquent->name = $fileItinerary->name->value();
        $fileItineraryEloquent->category = $fileItinerary->category->value();
        $fileItineraryEloquent->object_code = $fileItinerary->serviceCode->value();
        $fileItineraryEloquent->country_in_iso = $fileItinerary->countryInIso->value();
        $fileItineraryEloquent->country_in_name = $fileItinerary->countryInName->value();
        $fileItineraryEloquent->city_in_iso = $fileItinerary->cityInIso->value();
        $fileItineraryEloquent->city_in_name = $fileItinerary->cityInName->value();
        $fileItineraryEloquent->zone_in_iso = $fileItinerary->zoneInIso->value();

        $fileItineraryEloquent->zone_in_id = $fileItinerary->zoneInId->value();
        $fileItineraryEloquent->zone_in_airport = $fileItinerary->zoneInAirport->value();

        $fileItineraryEloquent->country_out_iso = $fileItinerary->countryOutIso->value();
        $fileItineraryEloquent->country_out_name = $fileItinerary->countryOutName->value();
        $fileItineraryEloquent->city_out_iso = $fileItinerary->cityOutIso->value();
        $fileItineraryEloquent->city_out_name = $fileItinerary->cityOutName->value();
        $fileItineraryEloquent->zone_out_iso = $fileItinerary->zoneOutIso->value();

        $fileItineraryEloquent->zone_out_id = $fileItinerary->zoneOutId->value();
        $fileItineraryEloquent->zone_out_airport = $fileItinerary->zoneOutAirport->value();

        $fileItineraryEloquent->start_time = $fileItinerary->startTime->value();
        $fileItineraryEloquent->departure_time = $fileItinerary->departureTime->value();


        $fileItineraryEloquent->date_in = $fileItinerary->dateIn->value();
        $fileItineraryEloquent->date_out = $fileItinerary->dateOut->value();
        $fileItineraryEloquent->total_adults = $fileItinerary->totalAdults->value();
        $fileItineraryEloquent->total_children = $fileItinerary->totalChildren->value();
        $fileItineraryEloquent->total_infants = $fileItinerary->totalInfants->value();
        $fileItineraryEloquent->markup_created = $fileItinerary->markupCreated->value();
        $fileItineraryEloquent->total_amount = $fileItinerary->totalAmount->value();
        $fileItineraryEloquent->total_cost_amount = $fileItinerary->totalCostAmount->value();
        $fileItineraryEloquent->serial_sharing = $fileItinerary->serialSharing->value();
        $fileItineraryEloquent->executive_code = $fileItinerary->executiveCode->value();
        $fileItineraryEloquent->status = $fileItinerary->status->value();
        $fileItineraryEloquent->confirmation_status = $fileItinerary->confirmationStatus->value();
        $fileItineraryEloquent->policies_cancellation_service = $fileItinerary->policiesCancellationService->value();
        $fileItineraryEloquent->data_passengers = $fileItinerary->dataPassengers->value();
        $fileItineraryEloquent->service_rate_id = $fileItinerary->serviceRateId->value();
        $fileItineraryEloquent->is_in_ope = $fileItinerary->isInOpe->value();
        $fileItineraryEloquent->sent_to_ope = $fileItinerary->sentToOpe->value();
        $fileItineraryEloquent->hotel_origin = $fileItinerary->hotelOrigin->value();
        $fileItineraryEloquent->hotel_destination = $fileItinerary->hotelDestination->value();
        $fileItineraryEloquent->service_supplier_code = $fileItinerary->serviceSupplierCode->value();
        $fileItineraryEloquent->service_supplier_name = $fileItinerary->serviceSupplierName->value();
        $fileItineraryEloquent->protected_rate = $fileItinerary->protectedRate->value();
        $fileItineraryEloquent->view_protected_rate = $fileItinerary->viewProtectedRate->value();
        $fileItineraryEloquent->service_category_id = $fileItinerary->serviceCategoryId->value();
        $fileItineraryEloquent->service_sub_category_id = $fileItinerary->serviceSubCategoryId->value();
        $fileItineraryEloquent->service_type_id = $fileItinerary->serviceTypeId->value();
        $fileItineraryEloquent->service_summary = $fileItinerary->serviceSummary->value();
        $fileItineraryEloquent->service_itinerary = $fileItinerary->serviceItinerary->value();
        $fileItineraryEloquent->add_to_statement = $fileItinerary->addToStatement->value();
        $fileItineraryEloquent->aurora_reservation_id = $fileItinerary->auroraReservationId->value();
        $fileItineraryEloquent->files_ms_parameters = $fileItinerary->filesMsParameters->value();

        return $fileItineraryEloquent;
    }

    public static function fromRequestUpdateSchedule(Request $request): array
    {

        $new_time_start = $request->input('start_time', null);
        $new_time_end = $request->input('departure_time', null);

        if($new_time_start === null){
            throw new \DomainException("null hours");
        }

        return [
            'start_time' => new StartTime($new_time_start),
            'departure_time' => new DepartureTime($new_time_end),
        ];

        // $new_time = $request->input('new_time', date("H:i:s"));
        // return new StartTime($new_time);
    }

    public static function fromRequestUpdatePassengers(Request $request): array
    {
        return $request->input('passengers', []);
    }

    public static function fromRequestUpdateAmountCost(Request $request): array
    {
        $file_amount_reason_id = $request->input('file_amount_reason_id', 0);
        $file_amount_type_flag_id = $request->input('file_amount_type_flag_id', 0);
        $amount_sale = $request->input('new_amount_sale', 0);

        return [
            'file_amount_reason_id' => $file_amount_reason_id,
            'file_amount_type_flag_id' => $file_amount_type_flag_id,
            'amount_sale' => $amount_sale,
        ];
    }

}
