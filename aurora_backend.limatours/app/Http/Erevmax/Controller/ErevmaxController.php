<?php

namespace App\Http\Erevmax\Controller;

use App\Http\Controllers\Controller;
use App\Http\Erevmax\Traits\Erevmax;
use App\Rates;
use App\RatesPlansCalendarys;
use App\RatesPlansRooms;
use App\Http\Traits\ChannelLogs;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use SimpleXMLElement;
use stdClass;
use Symfony\Component\HttpFoundation\ServerBag;

class ErevmaxController extends Controller
{
    use Erevmax;
//    use ChannelLogs;

    const LIMATOURS_ID = 'LIMATOURS';    // if supply by the conection
    const CONECTOR_CODE = 'EREVMAX';    // System currently unavailable
    const WSDL = "http://schemas.xmlsoap.org/wsdl/";
    const SOAPENV = "http://schemas.xmlsoap.org/soap/envelope/";
    const WSSE = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
    const WSA = "http://schemas.xmlsoap.org/ws/2004/08/addressing";
    const OTA = "http://www.opentravel.org/OTA/2003/05";
    const TNS = "http://127.0.0.1:8000/api/siteminder";
    const XS = "http://www.w3.org/2001/XMLSchema";

    const AUTHENTICATION = 4;    // System currently unavailable

    const SYSTEM_CORRENTLY_UNAVAILABLE = 187;    // System currently unavailable
    const INVALID_PROPERTY_CODE = 400;    // Invalid property code Hotel code
    const SYSTEM_ERROR = 448;    // System error
    const UNABLE_TO_PROCESS = 450;    // Unable to process

    const INVALID_RATE_CODE = 249;    // Invalid rate code
    const REQUIRED_FIELD_MISSING = 321;    // Required field missing
    const HOTEL_NOT_ACTIVE = 375;    // Hotel not active
    const INVALID_HOTEL_CODE = 392;    // Invalid hotel code
    const INVALID_ROOM_TYPE = 402;    // Invalid room type
    const RATE_DOES_NOT_EXIST = 436;    // Rate does not exist
    const ROOM_OR_RATE_NOT_FOUND = 783;    // Room or rate not found
    const RATE_NOT_LOADED = 842;    // Rate not loaded

    /**
     * @param $version
     * @return ResponseFactory|Response
     */
    public function index(Request $request, $version)
    {
        try {
//            $Erevmax =  \App\Http\Erevmax\Erevmax::server($request);
//
//            $response = response($Erevmax->responseXML(), 200, [
//                'Content-Type' => 'text/xml; charset=utf-8'
//            ]);
//
//            $this->putXmlLog($request, $response, $Erevmax->getChannel(), $Erevmax->getAction(), $Erevmax->getEchoToken(), true);
//
//            return $response;

            $this->setChannel(self::CONECTOR_CODE);
            $this->setXMLSoapRequest($request->getContent());

            $this->setHeader();
            $this->setBody();

            $this->setAction();
            $this->setFrom();
            $this->setTo();
            $this->setReplyTo();
            $this->setMessageID();

            $this->setMustUnderstand();
            $this->setUsername();
            $this->setPassword();

            $this->setXMLRequest($this->getBodyChld(self::OTA)->asXML());
            if (method_exists($this, $this->requestedMethodName())) {
                $this->setVersion();
                $this->setTimeStamp();
                $this->setEchoToken();
                $this->setPrimaryLangID();
                $this->setTarget();

                $credentials = ['code' => $this->getUsername(), 'password' => $this->getPassword()];
                if (!$token = auth()->attempt($credentials)) {
                    throw new Exception('Unauthorized', self::AUTHENTICATION);
                }

                DB::beginTransaction();
                $xmlResponse = $this->responseRequest($this->{$this->requestedMethodName()}(), true);
                DB::commit();
                $success = true;
            } else {
                throw new Exception('Method Not Allow');
            }
        } catch (Exception $ex) {
            if ($this->requestedMethodName()) {
                $xmlResponse = $this->responseRequest(
                    $this->responseError(
                        $ex->getMessage(),
                        $ex->getCode()
                    ),
                    true
                );
                DB::rollBack();
            } else {
                $xmlResponse = $this->responseRequest($ex->getMessage());
            }

            $success = true;
        }

        $response = response($xmlResponse, 200, [
            'Content-Type' => 'text/xml; charset=utf-8'
        ]);

        $this->putXmlLog($request, $response, $this->getChannel(), $this->requestedMethodName(), $this->getEchoToken(), $success);

        return $response;
    }
    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelRatePlanRQ()
    {
        $AvailRatesOnly = (bool)(strtolower($this->getXMLRequest()->attributes()->AvailRatesOnly) === 'true');

        $this->setRequestedHotel($this->getXMLRequest()->RatePlans->RatePlan->HotelRef);
        $this->setRequestedRoomRates($this->getXMLRequest()->RatePlans->RatePlan);

        if (isset($this->getXMLRequest()->RatePlans->RatePlan->DateRange)) {
            $this->setStartEndDates($this->getXMLRequest()->RatePlans->RatePlan->DateRange);
        }

        $this->setAvailableRatesPlansRooms();
        $xml = $this->setXMLResponse();
        $xml->addChild('Success');
        if ($this->getAvailableRatesPlansRooms()->count() > 0) {
            $this->setRoomDescriptions($this->getHotelId());

            $RatePlans = $xml->addChild('RatePlans');
            $RatePlans->addAttribute('HotelCode', $this->getHotelCode());
//            dd($this->getAvailableRatesPlansRooms()->toArray());
            foreach ($this->getAvailableRatesPlansRooms() as $rate_plan_code => $RatePlanRooms) {
                $_RatePlan = $RatePlanRooms->first()->rate_plan;

                $RatePlan = $RatePlans->addChild('RatePlan');
                $RatePlan->addAttribute('CurrencyCode', 'USD');
//                $RatePlan->addAttribute('RatePlanCode', $rate_plan_code);
                $RatePlan->addAttribute('RatePlanCode', $_RatePlan['code']);
                $RatePlan->addAttribute('RatePlanNotifType', 'New');

                if ($this->getDateRange()) {
                    $RatePlan->addAttribute('Start', $this->getStartDate());
                    $RatePlan->addAttribute('End', $this->getEndDate());
                }

                $Rates = $RatePlan->addChild('Rates');
                foreach ($RatePlanRooms as $RatePlanRoom) {
                    $Room = $RatePlanRoom->room;
                    if (!$this->getDateRange()) {
                        $Rate = $Rates->addChild('Rate');
                        $Rate->addAttribute('InvCode', $Room->channel_room->code);
                        $Rate->addAttribute('InvTypeCode', $Room->channel_room->code);
//                        $Rate->addAttribute('BaseOccupancy', $Room->first()->max_capacity;
                        $Rate->addAttribute('MinOccupancy', $Room['min_adults']);
                        $Rate->addAttribute('MaxOccupancy', $Room['max_adults']);
                    } else {
                        $dates = $RatePlanRoom->calendarys->keyBy('date');

                        $dateRangesRows = $this->getDateRangesRows($RatePlanRoom->id, $dates);
                        foreach ($dateRangesRows as $dateRangesRow) {
                            $Rate = $Rates->addChild('Rate');
                            $Rate->addAttribute('InvCode', $Room->channel_room->code);
                            $Rate->addAttribute('InvTypeCode', $Room->channel_room->code);
//                            $Rate->addAttribute('BaseOccupancy', $Room->first()->max_capacity;
                            $Rate->addAttribute('MinOccupancy', $Room['min_adults']);
                            $Rate->addAttribute('MaxOccupancy', $Room['max_adults']);
                            $Rate->addAttribute('Start', $dateRangesRow['Start']);
                            $Rate->addAttribute('End', $dateRangesRow['End']);

                            $BaseByGuestAmts = $Rate->addChild('BaseByGuestAmts');
                            if ($dateRangesRow['Rates']->count() == 0) {
                                $BaseByGuestAmt = $BaseByGuestAmts->addChild('BaseByGuestAmt');

                                $BaseByGuestAmt->addAttribute('AmountAfterTax', '0.00');
                                $BaseByGuestAmt->addAttribute('AgeQualifyingCode', 10);

                                $AdditionalGuestAmounts = $Rate->addChild('AdditionalGuestAmounts');

                                if ($this->hotel()->allows_child or $this->hotel()->allows_teenagers) {
                                    $AdditionalGuestAmount = $AdditionalGuestAmounts->addChild('AdditionalGuestAmount');
                                    $AdditionalGuestAmount->addAttribute('AgeQualifyingCode', 8);
                                    $AdditionalGuestAmount->addAttribute('Amount', '0.00');
                                }

                                $AdditionalGuestAmount = $AdditionalGuestAmounts->addChild('AdditionalGuestAmount');
                                $AdditionalGuestAmount->addAttribute('AgeQualifyingCode', 10);
                                $AdditionalGuestAmount->addAttribute('Amount', '0.00');
                            } else {
                                $AdditionalGuestAmount = false;
                                foreach ($dateRangesRow['Rates'] as $priceTipe => $ratesDate) {
                                    foreach ($ratesDate as $rateDate) {
                                        switch ($priceTipe) {
                                            case 'price_total':
                                                $BaseByGuestAmt = $BaseByGuestAmts->addChild('BaseByGuestAmt');
                                                $BaseByGuestAmt->addAttribute('AmountAfterTax', $rateDate['price_total']);
                                                $BaseByGuestAmt->addAttribute('AgeQualifyingCode', 10);
                                                break;
                                            case 'price_adult':
                                                $BaseByGuestAmt = $BaseByGuestAmts->addChild('BaseByGuestAmt');
                                                $BaseByGuestAmt->addAttribute('AmountAfterTax', $rateDate['price_adult']);
                                                $BaseByGuestAmt->addAttribute('NumberOfGuests', $rateDate['num_adult']);
                                                $BaseByGuestAmt->addAttribute('AgeQualifyingCode', 10);
                                                break;
                                            case 'price_child':
                                                if ($this->hotel()->allows_child or $this->hotel()->allows_teenagers) {
                                                    if ($rateDate['num_child'] != null and $rateDate['num_child'] > 0) {
                                                        $BaseByGuestAmt = $BaseByGuestAmts->addChild('BaseByGuestAmt');
                                                        $BaseByGuestAmt->addAttribute('AmountAfterTax', $rateDate['price_child']);
                                                        $BaseByGuestAmt->addAttribute('AgeQualifyingCode', 8);
                                                    }
                                                }
                                                break;
                                            case 'price_extra':
                                                if (!$AdditionalGuestAmount) {
                                                    $AdditionalGuestAmounts = $Rate->addChild('AdditionalGuestAmounts');
                                                }
                                                $AdditionalGuestAmount = $AdditionalGuestAmounts->addChild('AdditionalGuestAmount');
                                                $AdditionalGuestAmount->addAttribute('Amount', $rateDate['price_extra']);
                                                $AdditionalGuestAmount->addAttribute('AgeQualifyingCode', 10);
                                                break;
//                                            case 'price_extra_child':
//                                                if ($this->hotel()->allows_child or $this->hotel()->allows_teenagers) {
//                                                    if (!$AdditionalGuestAmount) {
//                                                        $AdditionalGuestAmounts = $Rate->addChild('AdditionalGuestAmounts');
//                                                    }
//                                                    if ($rateDate['num_child'] != null and $rateDate['num_child'] > 0) {
//                                                        $AdditionalGuestAmount = $AdditionalGuestAmounts->addChild('AdditionalGuestAmount');
//                                                        $AdditionalGuestAmount->addAttribute('Amount', $rateDate['price_extra_child']);
//                                                        $AdditionalGuestAmount->addAttribute('AgeQualifyingCode', 8);
//                                                    }
//                                                }
//                                                break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $Description = $RatePlan->addChild('Description');
                $Description->addAttribute('Name', 'Short Description');
                $Description->addChild('Text', $_RatePlan['name']);

                $Description = $RatePlan->addChild('Description');
                $Description->addAttribute('Name', 'Long Description');
                $Description->addChild('Text', $_RatePlan['name']);
            }
        }

        return $xml;
    }

    /**
     * Funcion especifivamente creada para el metoso OTA_HotelRatePlanRQ de Erevmax
     *
     * Se usa para agrupar por rango de fechas lasmtarifas consecutivas que sean iguales
     * Tambien llega los rangos de fechas que no se encuentren en la coleccion $dates como tarifas vacias
     *
     * @param $ratesPlansRoomId
     * @param Collection $dates
     * @return Collection
     */
    private function getDateRangesRows($ratesPlansRoomId, Collection $dates)
    {
        $prevDateRow = null;
        $currentStartDateRow = null;
        $dateRangesRows = collect();
        $dateRangeIndex = 0;
        if ($dates->count() == 0) {
            $dateRangesRows->add($this->addDateRangeRow(
                $ratesPlansRoomId,
                $this->getStartDate(),
                $this->getEndDate(),
                collect()
            ));
        } else {
            foreach ($dates as $currentDateRow) {
                $currentDateRow->rate->transform(function ($item) {
                    return $item->only(['num_adult', 'num_child', 'num_infant', 'price_adult', 'price_child', 'price_infant', 'price_extra', 'price_total']);
                });
                $currentDateRow->rate = $currentDateRow->rate->mapToGroups(function ($item, $key) {
                    $return = [];
                    if ($item['price_total'] != null) {
                        $return['price_total'] = $item;
                    }
                    if ($item['num_adult'] != null and $item['num_adult'] > 0) {
                        $return['price_adult'] = $item;
                    }
                    if ($item['price_extra'] != null) {
                        $return['price_extra'] = $item;
                    }
                    if ($item['num_child'] != null) {
                        $return['price_child'] = $item;
                    }
                    return $return;
                });

                // Si el offset no existe es por que es el primer elemento  y agregaremos el primer rango
                if (!$dateRangesRows->offsetExists($dateRangeIndex)) {
                    // si la fecha del primer elemento es diferente a la fecha de inicio entonces debemo agregar un primer
                    // rango de fechas que anterior a la fecha del primer elemento
                    if ($currentDateRow->date != $this->getStartDate()) {
                        $dateRangesRows->offsetSet($dateRangeIndex, $this->addDateRangeRow(
                            $ratesPlansRoomId,
                            $this->getStartDate(),
                            Carbon::parse($currentDateRow->date)->subDay()->format('Y-m-d'),
                            collect()
                        ));
                        $dateRangeIndex++;
                    }

                    $dateRangesRows->offsetSet($dateRangeIndex, $this->addDateRangeRow(
                        $ratesPlansRoomId,
                        $currentDateRow->date,
                        $currentDateRow->date,
                        $currentDateRow->rate
                    ));
                } else {
                    // calcular la fecha siguiente a $currentDateRow->date para comprobar que la fecha actual es
                    // consecutiva a la fecha anterior
                    $nexToPrevDate = Carbon::parse($dateRangesRows[$dateRangeIndex]['End'])->addDay()->format('Y-m-d');
                    // si la fecha del elemento actual es diferente a la fecha consecutiva o
                    // las tarifas del elemento son distintas al la del offset actual debemos
                    // agregar un nuevo rango
                    if (
                        $nexToPrevDate != $currentDateRow->date or
                        $dateRangesRows[$dateRangeIndex]['Rates']->toArray() != $currentDateRow->rate->toArray()
                    ) {
                        if ($nexToPrevDate != $currentDateRow->date) {
                            // si la fecha de $currentDateRow no es la que deberia ser la siguiente debamos
                            // calcular el rango de fechas entre $nexToPrevDate y la fecha antes de $currentDateRow->date
                            $dateRangeIndex++;
                            $dateRangesRows->offsetSet($dateRangeIndex, $this->addDateRangeRow(
                                $ratesPlansRoomId,
                                $nexToPrevDate,
                                Carbon::parse($currentDateRow->date)->subDay()->format('Y-m-d'),
                                collect()
                            ));
                        }
                        $dateRangeIndex++;
                        $dateRangesRows->offsetSet($dateRangeIndex, $this->addDateRangeRow(
                            $ratesPlansRoomId,
                            $currentDateRow->date,
                            $currentDateRow->date,
                            $currentDateRow->rate
                        ));
                    } else {
                        $dateRangesRows[$dateRangeIndex]['End'] = $currentDateRow->date;
                    }
                }
            }
            // si el rango de fechas iniciarl no inicia en  $this->getStartDate() debemos
            // agregar al inicio el rango de fechas que esta por fuera
            if ($dateRangesRows->first()['Start'] != $this->getStartDate()) {
                $dateRangesRows->prepend($this->addDateRangeRow(
                    $ratesPlansRoomId,
                    $this->getStartDate(),
                    Carbon::parse($dateRangesRows->first()['Start'])->subDay()->format('Y-m-d'),
                    collect()
                ));
            }

            // si el rango de fechas final no termina en  $this->getEndDate() debemos
            // agregar el rango de fechas que esta por fuera
            if ($dateRangesRows->last()['End'] != $this->getEndDate()) {
                $dateRangesRows->add($this->addDateRangeRow(
                    $ratesPlansRoomId,
                    Carbon::parse($dateRangesRows->last()['End'])->addDay()->format('Y-m-d'),
                    $this->getEndDate(),
                    collect()
                ));
            }
        }

        return $dateRangesRows;
    }

    private function addDateRangeRow($ratesPlansRoomId, $start, $end, Collection $dateRates)
    {
        return collect([
            'rates_plans_room_id' => $ratesPlansRoomId,
            'Start' => $start,
            'End' => $end,
            'Rates' => $dateRates,
        ]);
    }

    /**
     * @return mixed
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelAvailGetRQ()
    {
        $xml = $this->setXMLResponse();
        $xml->addChild('Success');
        /**
         * $Unique_ID : The unique identifier element allows the trading
         * partners to uniquely identify each AvailStatusMessage
         * for tracing of transactions.
         */
        $Unique_ID = 1;
        foreach ($this->getXMLRequest()->HotelAvailRequests->HotelAvailRequest as $HotelAvailRequest) {
            $SendBookingLimit = false;
            if ($HotelAvailRequest->attributes()->SendBookingLimit) {
                $SendBookingLimit = (bool)$HotelAvailRequest->attributes()->SendBookingLimit->__toString();
            }

            $SendAllRestrictions = false;
            if (isset($HotelAvailRequest->RestrictionStatusCandidates) and
                isset($HotelAvailRequest->RestrictionStatusCandidates->attributes()->SendAllRestrictions)) {
                $SendAllRestrictions = (bool)$HotelAvailRequest->RestrictionStatusCandidates->attributes()->SendAllRestrictions->__toString();
            }

            $this->setRequestedHotel($HotelAvailRequest->HotelRef);
            $this->setStartEndDates($HotelAvailRequest->DateRange);
            $this->setRequestedRoomRates($HotelAvailRequest);
            $this->setAvailableRatesPlansRooms($inventory = true);

            $AvailStatusMessages = $xml->addChild('AvailStatusMessages');
            $AvailStatusMessages->addAttribute('HotelCode', $this->getHotelCode());
            foreach ($this->getAvailableRatesPlansRooms() as $RatesPlansRooms) {
                foreach ($RatesPlansRooms as $RatePlanRoom) {
                    $RatePlanCode = $RatePlanRoom->rate_plan->code;
                    $InvTypeCode = $RatePlanRoom->room->channel_room->code;

                    $calendary = $RatePlanRoom->calendarys->keyBy('date');
                    $inventories = $RatePlanRoom->inventories->keyBy('date');

                    $dateRangesRows = $this->getDateRangesRows2($RatePlanRoom->id, $calendary, $inventories);
                    foreach ($dateRangesRows as $dateRangesRow) {

                        $AvailStatusMessage = $AvailStatusMessages->addChild('AvailStatusMessage');

                        $UniqueID = $AvailStatusMessage->addChild('UniqueID');
                        $UniqueID->addAttribute('ID', $Unique_ID);
                        $UniqueID->addAttribute('Type', '16');
                        $Unique_ID++;

                        if ($SendBookingLimit) {
                            $AvailStatusMessage->addAttribute(
                                'BookingLimit',
                                $dateRangesRow['Inventory']['inventory_num'] ? (int)$dateRangesRow['Inventory']['inventory_num'] : 0
                            );
                            $AvailStatusMessage->addAttribute('BookingLimitMessageType', 'SetLimit');
                        }

                        $StatusApplicationControl = $AvailStatusMessage->addChild('StatusApplicationControl');
                        $StatusApplicationControl->addAttribute('Start', $dateRangesRow['Start']);
                        $StatusApplicationControl->addAttribute('End', $dateRangesRow['End']);
                        $StatusApplicationControl->addAttribute('RatePlanCode', $RatePlanCode);
                        $StatusApplicationControl->addAttribute('InvTypeCode', $InvTypeCode);

                        if ($SendAllRestrictions) {
                            $LengthsOfStay = $AvailStatusMessage->addChild('LengthsOfStay');
                            $LengthOfStay = $LengthsOfStay->addChild('LengthOfStay');
                            $LengthOfStay->addAttribute(
                                'Time',
                                $dateRangesRow['Rates']['min_length_stay'] ? (int)$dateRangesRow['Rates']['min_length_stay'] : 1
                            );
                            $LengthOfStay->addAttribute('TimeUnit', 'Day');
                            $LengthOfStay->addAttribute('MinMaxMessageType', 'SetMinLOS');

                            $LengthOfStay = $LengthsOfStay->addChild('LengthOfStay');
                            $LengthOfStay->addAttribute(
                                'Time',
                                $dateRangesRow['Rates']['max_length_stay'] ? (int)$dateRangesRow['Rates']['max_length_stay'] : 99
                            );
                            $LengthOfStay->addAttribute('TimeUnit', 'Day');
                            $LengthOfStay->addAttribute('MinMaxMessageType', 'SetMaxLOS');
                        }

                        if ($SendAllRestrictions) {
//                            $RestrictionStatus = $AvailStatusMessage->addChild('RestrictionStatus');
//                            $RestrictionStatus->addAttribute('Restriction', 'Arrival');
//                            $RestrictionStatus->addAttribute('Status', ($dateRangesRow['Inventory']['locked']) ? 'Close' : 'Open');
//                            $RestrictionStatus = $AvailStatusMessage->addChild('RestrictionStatus');
//                            $RestrictionStatus->addAttribute('Restriction', 'Departure');
//                            $RestrictionStatus->addAttribute('Status', ($dateRangesRow['Inventory']['locked']) ? 'Close' : 'Open');
                            $RestrictionStatus = $AvailStatusMessage->addChild('RestrictionStatus');
                            $RestrictionStatus->addAttribute('Restriction', 'Master');
                            $RestrictionStatus->addAttribute('Status', ($dateRangesRow['Inventory']['locked']) ? 'Close' : 'Open');

                            $RestrictionStatus = $AvailStatusMessage->addChild('RestrictionStatus');
                            $RestrictionStatus->addAttribute(
                                'MinAdvancedBookingOffset',
                                ($dateRangesRow['Rates']['min_ab_offset']) ? 'P' . $dateRangesRow['Rates']['min_ab_offset'] . 'D' : 'P1D'
                            );
                            $RestrictionStatus->addAttribute(
                                'MaxAdvancedBookingOffset',
                                ($dateRangesRow['Rates']['max_ab_offset']) ? 'P' . $dateRangesRow['Rates']['max_ab_offset'] . 'D' : 'P365D'
                            );
                        }
                    }
                }
            }
        }

        return $xml;
    }

    /**
     * Funcion especifivamente creada para el metoso OTA_HotelRatePlanRQ de Erevmax
     *
     * Se usa para agrupar por rango de fechas lasmtarifas consecutivas que sean iguales
     * Tambien llega los rangos de fechas que no se encuentren en la coleccion $dates como tarifas vacias
     *
     * @param $ratesPlansRoomId
     * @param Collection $calendary
     * @param Collection $inventories
     * @return Collection
     */
    private function getDateRangesRows2($ratesPlansRoomId, Collection $calendary, Collection $inventories)
    {
        $prevDateRow = null;
        $currentStartDateRow = null;
        $dateRangesRows = collect();
        $dateRangeIndex = 0;

        $emtyCalen = collect([
            'max_ab_offset' => 365,
            'min_ab_offset' => 1,
            'min_length_stay' => 1,
            'max_length_stay' => 99,
        ]);
        $emtyInvent = collect([
            'inventory_num' => 0,
            'total_booking' => 0,
            'total_canceled' => 0,
            'locked' => 0,
        ]);

        if ($calendary->count() == 0 and $inventories->count() == 0) {
            $dateRangesRows->add($this->addDateRangeRow2(
                $ratesPlansRoomId,
                $this->getStartDate(),
                $this->getEndDate(),
                $emtyCalen,
                $emtyInvent
            ));
        } else {
            foreach ($this->getDateRange() as $date) {
                $currentCalendaryRow = $emtyCalen;
                $currentInventoryRow = $emtyInvent;
                $dateInput = $date->format("Y-m-d");
                if ($calendary->offsetExists($dateInput)) {
                    $currentCalendaryRow = collect($calendary->offsetGet($dateInput)->only([
                        'max_ab_offset',
                        'min_ab_offset',
                        'min_length_stay',
                        'max_length_stay',
                    ]));
                }

                if ($inventories->offsetExists($dateInput)) {
                    $currentInventoryRow = collect($inventories->offsetGet($dateInput)->only([
                        'inventory_num',
                        'total_booking',
                        'total_canceled',
                        'locked',
                    ]));
                }

                // Si el offset no existe es por que es el primer elemento  y agregaremos el primer rango
                if (!$dateRangesRows->offsetExists($dateRangeIndex)) {
                    $dateRangesRows->offsetSet($dateRangeIndex, $this->addDateRangeRow2(
                        $ratesPlansRoomId,
                        $dateInput,
                        $dateInput,
                        $currentCalendaryRow,
                        $currentInventoryRow
                    ));
                } else {
                    // si las restricciones o el inventario del elemento actual son distintas a las del
                    // offset actual debemos agregar un nuevo rango
                    if (
                        $dateRangesRows[$dateRangeIndex]['Rates']->toArray() != $currentCalendaryRow->toArray() or
                        $dateRangesRows[$dateRangeIndex]['Inventory']->toArray() != $currentInventoryRow->toArray()
                    ) {
                        $dateRangeIndex++;
                        $dateRangesRows->offsetSet($dateRangeIndex, $this->addDateRangeRow2(
                            $ratesPlansRoomId,
                            $dateInput,
                            $dateInput,
                            $currentCalendaryRow,
                            $currentInventoryRow
                        ));
                    } else {
                        $dateRangesRows[$dateRangeIndex]['End'] = $dateInput;
                    }
                }
            }
        }

        return $dateRangesRows;
    }


    private function addDateRangeRow2($ratesPlansRoomId, $start, $end, Collection $dateRates, Collection $inventory = null)
    {
        return collect([
            'rates_plans_room_id' => $ratesPlansRoomId,
            'Start' => $start,
            'End' => $end,
            'Rates' => $dateRates,
            'Inventory' => $inventory
        ]);
    }
    /**
     * @param $data
     * @return mixed
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelAvailNotifRQ()
    {
        $AvailRatesOnly = (bool)(strtolower($this->getXMLRequest()->attributes()->AvailRatesOnly) === 'true');
        $this->setRequestedHotel($this->getXMLRequest()->AvailStatusMessages);
        $datesCollection = collect();
        foreach ($this->getXMLRequest()->AvailStatusMessages->AvailStatusMessage as $AvailStatusMessage) {
            if (!isset($AvailStatusMessage->StatusApplicationControl)) {
                throw new Exception('Can not be null ' . __FUNCTION__ . 'AvailStatusMessages.AvailStatusMessage.StatusApplicationControl', self::REQUIRED_FIELD_MISSING);
            }

            $DateRange = $AvailStatusMessage->StatusApplicationControl;
            if (empty($DateRange->attributes()->Start) or empty($DateRange->attributes()->End)) {
                throw new Exception('Start and End date can not null or empty', self::REQUIRED_FIELD_MISSING);
            }

            $datesCollection->push(trim($DateRange->attributes()->Start->__toString()));
            $datesCollection->push(trim($DateRange->attributes()->End->__toString()));

            if (!empty($AvailStatusMessage->StatusApplicationControl->attributes()->RatePlanCode)) {
                $RatePlanCode = $AvailStatusMessage->StatusApplicationControl->attributes()->RatePlanCode->__toString();
                $this->requestedRoomRates['Rates'][$RatePlanCode] = $RatePlanCode;
            }
            if (!empty($AvailStatusMessage->StatusApplicationControl->attributes()->InvTypeCode)) {
                $InvTypeCode = $AvailStatusMessage->StatusApplicationControl->attributes()->InvTypeCode->__toString();
                $this->requestedRoomRates['Rooms'][$InvTypeCode] = $InvTypeCode;
            }
        }

        $datesCollection = $datesCollection->sort();
        $this->setStartDate($datesCollection->first());
        $this->setEndDate($datesCollection->last());
        $this->setDateRange($this->getStartDate(), $this->getEndDate());

        $this->setAvailableRatesPlansRooms(true);
        foreach ($this->getXMLRequest()->AvailStatusMessages->AvailStatusMessage as $AvailStatusMessage) {
            $messages = array();
            $StatusApplicationControl = $AvailStatusMessage->StatusApplicationControl->attributes();
            $this->setStartEndDates($AvailStatusMessage->StatusApplicationControl);

            $InvTypeCode = null;
            if (!empty($StatusApplicationControl->InvTypeCode)) {
                $InvTypeCode = $StatusApplicationControl->InvTypeCode->__toString();
            }
            $RatePlanCode = null;
            if (!empty($StatusApplicationControl->RatePlanCode)) {
                $RatePlanCode = $StatusApplicationControl->RatePlanCode->__toString();
            }

            if (!empty($StatusApplicationControl->Mon)) {
                if ((bool)$StatusApplicationControl->Mon->__toString()) {
                    $messages['dias'][] = "Mon";
                }
            }
            if (!empty($StatusApplicationControl->Tue)) {
                if ((bool)$StatusApplicationControl->Tue->__toString()) {
                    $messages['dias'][] = "Tue";
                }
            }
            if (!empty($StatusApplicationControl->Weds)) {
                if ((bool)$StatusApplicationControl->Weds->__toString()) {
                    $messages['dias'][] = "Wed";
                }
            }
            if (!empty($StatusApplicationControl->Thur)) {
                if ((bool)$StatusApplicationControl->Thur->__toString()) {
                    $messages['dias'][] = "Thu";
                }
            }
            if (!empty($StatusApplicationControl->Fri)) {
                if ((bool)$StatusApplicationControl->Fri->__toString()) {
                    $messages['dias'][] = "Fri";
                }
            }
            if (!empty($StatusApplicationControl->Sat)) {
                if ((bool)$StatusApplicationControl->Sat->__toString()) {
                    $messages['dias'][] = "Sat";
                }
            }
            if (!empty($StatusApplicationControl->Sun)) {
                if ((bool)$StatusApplicationControl->Sun->__toString()) {
                    $messages['dias'][] = "Sun";
                }
            }

            if (empty($messages['dias'])) {
                $messages['dias'] = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            }

            if (isset($AvailStatusMessage->attributes()->BookingLimit)) {
                $messages['inventories']['inventory_num'] = (int)$AvailStatusMessage->attributes()->BookingLimit;
            }

            if (isset($AvailStatusMessage->LengthsOfStay)) {
                if (!isset($AvailStatusMessage->LengthsOfStay->LengthOfStay)) {
                    throw new Exception('LengthsOfStay.LengthOfStay', self::REQUIRED_FIELD_MISSING);
                }

                foreach ($AvailStatusMessage->LengthsOfStay->LengthOfStay as $LengthOfStay) {
                    $MinMaxMessageType = (string)$LengthOfStay->attributes()->MinMaxMessageType;
                    if ($MinMaxMessageType == "SetMinLOS") {
                        $messages['restrictions']['min_length_stay'] = empty($LengthOfStay->attributes()->Time) ? 0 : (int)$LengthOfStay->attributes()->Time;
                    } elseif ($MinMaxMessageType == "SetMaxLOS") {
                        $messages['restrictions']['max_length_stay'] = empty($LengthOfStay->attributes()->Time) ? 99 : (int)$LengthOfStay->attributes()->Time;
                    }
                }
            }

            if (isset($AvailStatusMessage->RestrictionStatus)) {
                foreach ($AvailStatusMessage->RestrictionStatus as $RestrictionStatus) {
                    if (isset($RestrictionStatus->attributes()->Restriction) and strtolower($RestrictionStatus->attributes()->Restriction->__toString()) == "master") {
                        if (!isset($AvailStatusMessage->RestrictionStatus->attributes()->Status)) {
                            throw new Exception('RestrictionStatus.Status', self::REQUIRED_FIELD_MISSING);
                        }
                        $messages['inventories']['locked'] = (strtolower($AvailStatusMessage->RestrictionStatus->attributes()->Status)) == 'open' ? 0 : 1;
                    } else {
                        if (isset($RestrictionStatus->attributes()->MinAdvancedBookingOffset)) {
                            $messages['restrictions']['min_ab_offset'] = (new \DateInterval($RestrictionStatus->attributes()->MinAdvancedBookingOffset->__toString()))->format('%d');
                        }

                        if (isset($RestrictionStatus->attributes()->MaxAdvancedBookingOffset)) {
                            $messages['restrictions']['max_ab_offset'] = (new \DateInterval($RestrictionStatus->attributes()->MaxAdvancedBookingOffset->__toString()))->format('%d');
                        }
                    }
                }
            }

            if (!empty($RatePlanCode)) {
                if (!$this->getAvailableRatesPlansRooms()->offsetExists($RatePlanCode)) {
                    throw new Exception('RatePlanCode ' . $RatePlanCode . ' not found', self::ROOM_OR_RATE_NOT_FOUND);
                }
            }

            foreach ($this->getAvailableRatesPlansRooms() as $ind => $RatePlanRooms) {
                if (!empty($RatePlanCode)) {
                    if ($ind != $RatePlanCode) {
                        continue;
                    }
                }
                if (!empty($InvTypeCode)) {
                    if (!$RatePlanRooms->contains('room.channel_room.code', $InvTypeCode)) {
                        throw new Exception('RatePlanCode="' . $ind . '" and InvTypeCode="' . $InvTypeCode . '" combination not found', self::ROOM_OR_RATE_NOT_FOUND);
                    };
                }
                foreach ($RatePlanRooms as $RatePlanRoom) {
                    if (!empty($InvTypeCode)) {
                        if ($RatePlanRoom->room->channel_room->code != $InvTypeCode) {
                            continue;
                        }
                    }

                    foreach ($this->getDateRange() as $date) {
                        $dateInput = $date->format("Y-m-d");
                        if (!in_array(date('D', strtotime($dateInput)), $messages['dias'])) {
                            continue;
                        }

                        if (isset($messages['inventories'])) {
                            $invenrotyDate = $RatePlanRoom->getInventoriDate($dateInput);
                            if (!$invenrotyDate) {
                                $invenrotyDate = $RatePlanRoom->addInventoriDate($dateInput);
                            }

                            if (!empty($messages['inventories']['inventory_num'])) {
                                $invenrotyDate->inventory_num = $messages['inventories']['inventory_num'];
                            }
                            if (isset($messages['inventories']['locked'])) {
                                $invenrotyDate->locked = (int)$messages['inventories']['locked'];
                            }
                        }

                        if (isset($messages['restrictions'])) {
                            $calendaryDate = $RatePlanRoom->getCalendaryDate($dateInput);
                            if (!$calendaryDate) {
                                $calendaryDate = $RatePlanRoom->addCalendaryDate($dateInput);
                            }

                            if (!empty($messages['restrictions']['min_length_stay'])) {
                                $calendaryDate->min_length_stay = $messages['restrictions']['min_length_stay'];
                            }
                            if (!empty($messages['restrictions']['max_length_stay'])) {
                                $calendaryDate->max_length_stay = $messages['restrictions']['max_length_stay'];
                            }
                            if (!empty($messages['restrictions']['min_ab_offset'])) {
                                $calendaryDate->min_ab_offset = $messages['restrictions']['min_ab_offset'];
                            }
                            if (!empty($messages['restrictions']['max_ab_offset'])) {
                                $calendaryDate->max_ab_offset = $messages['restrictions']['max_ab_offset'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($this->getAvailableRatesPlansRooms() as $RatePlanRooms) {
            foreach ($RatePlanRooms as $RatePlanRoom) {
                $RatePlanRoom->calendarys()->saveMany($RatePlanRoom->calendarys);
                $RatePlanRoom->inventories()->saveMany($RatePlanRoom->inventories);
            }
        }


        $xml = $this->setXMLResponse();
        $xml->addChild('Success');

        return $xml;
    }

    /**
     * @param SimpleXMLElement $xmlRequest
     * @return SimpleXMLElement
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelRatePlanNotifRQ()
    {
        $this->setRequestedHotel($this->getXMLRequest()->RatePlans);

        $requestedRoomRates = array();
        foreach ($this->getXMLRequest()->RatePlans->RatePlan as $RatePlan) {
            $RatePlanNotifType = strtolower($RatePlan->attributes()->RatePlanNotifType->__toString());
            $RatePlanCode = (string)$RatePlan->attributes()->RatePlanCode->__toString();

            $this->setRequestedRoomRates2($RatePlan);
            foreach ($RatePlan->Rates->Rate as $Rate) {
                $InvTypeCode = (string)$Rate->attributes()->InvTypeCode->__toString();
                $roomRateCode = $InvTypeCode . '|' . $RatePlanCode;
                $requestedRoomRates[$roomRateCode]['RatePlanNotifType'][] = $RatePlanNotifType;
                $requestedRoomRates[$roomRateCode]['RatePlanCode'] = trim(strtoupper($RatePlanCode));

                if (isset($RatePlan->Rates->Description->Text)) {
                    if ($RatePlan->Rates->Description->Text->__toString() != '') {
                        $requestedRoomRates[$roomRateCode]['RatePlanName'] = $RatePlan->Rates->Description->Text->__toString();
                    }
                }

                $requestedRoomRates[$roomRateCode]['InvTypeCode'] = trim(strtoupper($InvTypeCode));
                if (isset($Rate->RateDescription->Text)) {
                    if ($Rate->RateDescription->Text->__toString() != '') {
                        $requestedRoomRates[$roomRateCode]['InvTypeName'] = $Rate->RateDescription->Text->__toString();
                    }
                }

                if (!in_array($RatePlanNotifType, ['new', 'delta'])) {
                    continue;
                }

                $Start = (string)$Rate->attributes()->Start;
                $End = (string)$Rate->attributes()->End;

                if ($Start and $End) {
                    if (strtotime($Start) > strtotime($End)) {
                        throw new Exception(
                            'Start date cannot by higer than End date',
                            self::ROOM_OR_RATE_NOT_FOUND
                        );
                    }

                    if (!isset($requestedRoomRates[$roomRateCode]['Start']) or strtotime($requestedRoomRates[$roomRateCode]['Start']) > strtotime($Start)) {
                        $requestedRoomRates[$roomRateCode]['Start'] = $Start;
                    }

                    if (!isset($requestedRoomRates[$roomRateCode]['End']) or strtotime($requestedRoomRates[$roomRateCode]['End']) < strtotime($End)) {
                        $requestedRoomRates[$roomRateCode]['End'] = $End;
                    }
                }

                $rates = array();
                if (isset($Rate->BaseByGuestAmts->BaseByGuestAmt)) {
                    foreach ($Rate->BaseByGuestAmts->BaseByGuestAmt as $BaseByGuestAmt) {
                        if ((string)$BaseByGuestAmt->attributes()->AgeQualifyingCode == 10) { // adultos
                            if (isset($BaseByGuestAmt->attributes()->NumberOfGuests)) { // Logica por persona
                                $rates['price_adult'][] = [
                                    'num_adult' => (int)$BaseByGuestAmt->attributes()->NumberOfGuests,
                                    'price_adult' => (float)$BaseByGuestAmt->attributes()->AmountAfterTax,
                                ];
                            } else { // Logica por habitacion
                                $rates['price_total'][] = ['price_total' => (float)$BaseByGuestAmt->attributes()->AmountAfterTax];
                            }
                        } elseif ((string)$BaseByGuestAmt->attributes()->AgeQualifyingCode == 8) { // niños
                            $rates['price_child'][] = [
                                'num_child' => 1,
                                'price_child' => (float)$BaseByGuestAmt->attributes()->AmountAfterTax,
                            ];
                        }
                    }
                }

                if (isset($Rate->AdditionalGuestAmounts->AdditionalGuestAmount)) {
                    foreach ($Rate->AdditionalGuestAmounts->AdditionalGuestAmount as $BaseByGuestAmt) {
                        if (((int)$BaseByGuestAmt->attributes()->AgeQualifyingCode) == 10) { // adulto extra
                            $rates['price_extra'][] = ['price_extra' => (float)$BaseByGuestAmt->attributes()->Amount];
//                        } else if (((int)$BaseByGuestAmt->attributes()->AgeQualifyingCode) == 8) { // niño extra
//                            $rates['price_extra_child'][] = [
//                                'num_child' => 1,
//                                'price_extra_child' => (float)$BaseByGuestAmt->attributes()->Amount,
//                            ];
                        }
                    }
                }

                if (count($rates) > 0) {
                    $StartEnd = $Start . '|' . $End;
                    $requestedRoomRates[$roomRateCode]['DateRanges'][$StartEnd] = $rates;
                }
            }
        }

        $this->setAvailableRatesPlansRooms();
        foreach ($requestedRoomRates as $roomRateCode => $requestedRoomRateDates) {
            foreach ($requestedRoomRateDates['RatePlanNotifType'] as $RatePlanNotifType) {
                if ($RatePlanNotifType == "new") { // creae
                    throw new Exception('RatePlanNotifType="new" feature not allowed');
                    // TODO Refactorizar las forma en la que se crean las relaciones nuevas ya quedeben tener un a politica de cancelacion
//                    $this->createRoomRatePlan($requestedRoomRateDates);
//                    DB::commit();
//                    DB::beginTransaction();
//                    $this->setAvailableRatesPlansRooms();
                } elseif ($RatePlanNotifType == "overlay") { // actualizar
                    $this->updateRoomRatePlan($requestedRoomRateDates);
                } elseif ($RatePlanNotifType == "remove") {// desactivar
                    $this->removeRoomRatePlen($requestedRoomRateDates);
                } elseif ($RatePlanNotifType == "delta") {
                    $RatePlanCode = $requestedRoomRateDates['RatePlanCode'];
                    $RooomCode = $requestedRoomRateDates['InvTypeCode'];

                    $RatesPlansRoom = $this->getRatePlanRoom($RatePlanCode, $RooomCode)
                        ->setCalendaryRange($requestedRoomRateDates['Start'], $requestedRoomRateDates['End']);
                    foreach ($requestedRoomRateDates['DateRanges'] as $DateRanges => $rates) {
                        list($startDate, $endDate) = explode('|', $DateRanges);
                        $this->setDateRange($startDate, $endDate);
                        foreach ($this->getDateRange() as $date) {
                            /** @var DateTime $date */
                            $dateInput = $date->format("Y-m-d");

                            $dateRates = $RatesPlansRoom->getCalendaryDate($dateInput);
                            if (empty($dateRates)) { // create
                                $dateRates = $RatesPlansRoom->addCalendaryDate($dateInput);
                            }
                            foreach ($rates as $rateType => $ratesType) {
                                foreach ($ratesType as $rate) {
                                    $rateSelected = $dateRates->getRateByType($rateType, $rate);

                                    if (empty($rateSelected)) {// create
                                        $dateRates->addRate($rate);
                                    } else {
                                        $rateSelected->fill($rate);
                                    }
                                }
                            }
                        }
                    }

                    $RatesPlansRoom->calendarys()->saveMany($RatesPlansRoom->calendarys);
                    foreach ($RatesPlansRoom->calendarys as $calendary) {
                        $calendary->rate()->saveMany($calendary->rate);
                    }
                }
            }
        }

        $xml = $this->setXMLResponse();
        $xml->addChild('Success');

        return $xml;
    }
}
