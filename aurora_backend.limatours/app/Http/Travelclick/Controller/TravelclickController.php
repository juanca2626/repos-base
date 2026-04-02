<?php

namespace App\Http\Travelclick\Controller;

use App\Http\Controllers\Controller;
use App\Http\Travelclick\Traits\Travelclick;
use App\Http\Traits\Channel;
use App\Http\Traits\ChannelLogs;
use Carbon\Carbon;
use DateInterval;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class TravelclickController extends Controller
{
    use Travelclick;
//    use Channel;

    const LIMATOURS_ID = 'LIMATOURS';    // if supply by the conection
    const CONECTOR_CODE = 'TRAVELCLICK';    // System currently unavailable
    const WSDL = "http://schemas.xmlsoap.org/wsdl/";
    const SOAPENV = "http://schemas.xmlsoap.org/soap/envelope/";
    const WSSE = "http://docs.oasis-open.org/wss/2004/01/oasis200401-wss-wssecurity-secext-1.0.xsd";
    const WSA = "http://schemas.xmlsoap.org/ws/2004/08/addressing";
    const OTA = "http://www.opentravel.org/OTA/2003/05";
    const TNS = "http://127.0.0.1:8000/api/siteminder";
    const XS = "http://www.w3.org/2001/XMLSchema";
    const XSI = "http://www.w3.org/2001/XMLSchema-instance";

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
     * @param Request $request
     * @param $version
     * @return ResponseFactory|Response
     */
    public function index(Request $request, $version)
    {

        return response("", 200, [
            'Content-Type' => 'application/xml'
        ]);

        try {
            $this->setChannel(self::CONECTOR_CODE);

            $this->setXMLRequest($request->getContent());
            if (method_exists($this, $this->requestedMethodName())) {
                $this->setVersion();
                $this->setEchoToken();
                $this->setTimeStamp();

                if ($this->requestedMethodName() != 'OTA_PingRQ') {
                    $credentials = ['code' => trim($request->headers->get('php-auth-user')), 'password' => trim($request->headers->get('php-auth-pw'))];
                    if (!$token = auth()->attempt($credentials)) {
                        throw new Exception('Unauthorized', self::AUTHENTICATION);
                    }
                }

                DB::beginTransaction();
                $xmlResponse = $this->{$this->requestedMethodName()}();
                DB::commit();
                $success = true;
            } else {
                throw new Exception('Method Not Allow');
            }
        } catch (Exception $ex) {
            if ($this->requestedMethodName()) {
                DB::rollBack();
            } else {
                $this->createEchoToken();
            }

            $xmlResponse = $this->responseError(
                $ex->getMessage(),
                $ex->getCode()
            );
            $success = false;
        }

        $response = response($xmlResponse->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);

        $this->putXmlLog($request, $response, $this->getChannel(), $this->requestedMethodName(), $this->getEchoToken(), $success);

        return $response;
    }

    /**
     * ping method.
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_PingRQ()
    {
        if (!isset($this->getXMLRequest()->EchoData)) {
            throw new Exception('Espected ' . __FUNCTION__ . '.EchoData');
        }

        $xml = $this->setXMLResponse();
        $xml->addChild('Success');
        $xml->addChild("EchoData", $this->getXMLRequest()->EchoData->__toString());

        return $xml;
    }

    /**
     *  profile handling
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelProductRQ()
    {
        $this->setRequestedHotel($this->getXMLRequest()->HotelProducts);
        $this->setAvailableRooms();
        $xml = $this->setXMLResponse();
        $xml->addChild('Success');
        if (empty($this->getAvailableRooms())) {
            return $xml;
        }

        $HotelProducts = $xml->addChild("HotelProducts");
        foreach ($this->getAvailableRooms() as $RoomTypeCode => $room) {
            $roomRates = $room->rates_plan_room;
            if ($roomRates->count() == 0) {
                continue;
            }

            $roomDesc = $room->translations->groupBy(['slug']);

            $HotelProduct = $HotelProducts->addChild("HotelProduct");
            $RoomTypes = $HotelProduct->addChild("RoomTypes");
            $RoomType = $RoomTypes->addChild("RoomType");
            $RoomType->addAttribute('RoomTypeCode', $room->channels->first()->pivot->code);
            $RoomType->addAttribute('RoomTypeName', $roomDesc['room_name']->first()->value);

            $RatePlans = $HotelProduct->addChild("RatePlans");
            foreach ($roomRates as $roomRate) {
                $rate = $roomRate->rate_plan;

                $RatePlan = $RatePlans->addChild("RatePlan");
                $RatePlan->addAttribute('RatePlanCode', $rate->code);
                $RatePlan->addAttribute('RatePlanName', $rate->translations->first()->value);
                $RatePlan->addAttribute('RoomTypeName', 'NET');
                $RatePlan->addAttribute('CurrencyCode', 'USD');
            }
        }

        return $xml;
    }

    /**
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelInvCountNotifRQ()
    {
        $this->setRequestedHotel($this->getXMLRequest()->Inventories);
        $this->setAvailableRooms();

        $ratePlanRoomInserts = array();
        foreach ($this->getXMLRequest()->Inventories->Inventory as $Inventory) {
            $desdeFecha = $Inventory->StatusApplicationControl['Start']->__toString();
            $hastaFecha = $Inventory->StatusApplicationControl['End']->__toString();
            $InvTypeCode = $Inventory->StatusApplicationControl['InvTypeCode']->__toString();
            $RatePlanCode = $Inventory->StatusApplicationControl['RatePlanCode']->__toString();

            if (!$desdeFecha) {
                throw new Exception('Can not be null ' . __FUNCTION__ . '.Inventories.Inventory.StatusApplicationControl.@Start');
            }

            if (!$hastaFecha) {
                throw new Exception('Can not be null ' . __FUNCTION__ . '.Inventories.Inventory.StatusApplicationControl.@End');
            }

            if (!$InvTypeCode) {
                throw new Exception('Can not be null ' . __FUNCTION__ . '.Inventories.Inventory.StatusApplicationControl.@InvTypeCode');
            }

            if (!$RatePlanCode) {
                throw new Exception('Can not be null ' . __FUNCTION__ . '.Inventories.Inventory.StatusApplicationControl.@RatePlanCode');
            }

            $roomRatePlans = $this->getAvailableRooms()->first(function ($query, $key) use ($InvTypeCode) {
                return $query->channels[0]->pivot->code == $InvTypeCode;
            });

            if (empty($roomRatePlans)) {
                throw new Exception('Looks like InvTypeCode : ' . $InvTypeCode . ', is not valid ' . __FUNCTION__ . '.Inventories.Inventory.StatusApplicationControl.@InvTypeCode');
            }

            $ratePlanRoom = $roomRatePlans->rates_plan_room->first(function ($query, $key) use ($RatePlanCode) {
                return $query->rate_plan->code == $RatePlanCode;
            });

            if (empty($ratePlanRoom)) {
                throw new Exception('Looks like RatePlanCode : ' . $RatePlanCode . ', is not valid ' . __FUNCTION__ . '.Inventories.Inventory.StatusApplicationControl.@RatePlanCode');
            }

            $this->setDateRange($desdeFecha, $hastaFecha);

            $ratePlanRoomInventories = $this->getRatePlanRoomInventories($ratePlanRoom, ['Start' => $desdeFecha, 'End' => $hastaFecha]);

            $Count = $Inventory->InvCounts->InvCount['Count']->__toString();
//            $Type = $Inventory->InvCounts->InvCount['CountType']->__toString();
            if (!empty($ratePlanRoomInventories)) {
                foreach ($this->getDateRange() as $date) {
                    $dateInput = $date->format("Y-m-d");

                    $inventoriDate = $ratePlanRoomInventories->first(function ($query, $key) use ($dateInput) {
                        return $query->date == $dateInput;
                    });

                    if (empty($inventoriDate)) {
                        $ratePlanRoomInserts[$ratePlanRoom->id][$dateInput] = [
                            'rate_plan_rooms_id' => $ratePlanRoom->id,
                            'date' => $dateInput,
                            'day' => substr($dateInput, -2),
                            'inventory_num' => $Count,
                            'created_at'=> Carbon::now(),
                            'updated_at'=> Carbon::now()

                        ];
                    }
                }

                DB::table('inventories')
                    ->whereBetween('date', [$desdeFecha, $hastaFecha])
                    ->where('rate_plan_rooms_id', $ratePlanRoom->id)
                    ->update(['inventory_num' => $Count,
                            'created_at'=> Carbon::now(),
                            'updated_at'=> Carbon::now()]);
            } else {
                foreach ($this->getDateRange() as $date) {
                    $dateInput = $date->format("Y-m-d");
                    $ratePlanRoomInserts[$ratePlanRoom->id][$dateInput] = [
                        'rate_plan_rooms_id' => $ratePlanRoom->id,
                        'date' => $dateInput,
                        'day' => substr($dateInput, -2),
                        'inventory_num' => $Count,
                        'created_at'=> Carbon::now(),
                        'updated_at'=> Carbon::now()
                    ];
                }
            }
        }

        if (count($ratePlanRoomInserts) > 0) {
            foreach ($ratePlanRoomInserts as $dates) {
                DB::table('inventories')
                    ->insert($dates);
            }
        }

        $xml = $this->setXMLResponse();
        $xml->addChild('Success');

        return $xml;
    }

    /**
     * RatePlna Updates availability updates.
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelRatePlanNotifRQ()
    {
        $this->setRequestedHotel($this->getXMLRequest()->RatePlans);
        $this->setAvailableRooms();

        $ratePlanRoomCalendaryInserts = array();
        foreach ($this->getXMLRequest()->RatePlans->RatePlan as $RatePlan) {
            $desdeFecha = $RatePlan['Start']->__toString();
            $hastaFecha = $RatePlan['End']->__toString();
            $RatePlanCode = $RatePlan['RatePlanCode']->__toString();

            if (!$desdeFecha) {
                throw new Exception('Can not be null ' . __FUNCTION__ . '.RatePlans.RatePlan.@Start');
            }

            if (!$hastaFecha) {
                throw new Exception('Can not be null ' . __FUNCTION__ . '.RatePlans.RatePlan.@End');
            }

            if (!$RatePlanCode) {
                throw new Exception('Can not be null ' . __FUNCTION__ . '.RatePlans.RatePlan.@RatePlanCode');
            }

            $this->setDateRange($desdeFecha, $hastaFecha);
            foreach ($RatePlan->Rates->Rate as $Rate) {
                $InvTypeCode = $Rate['InvTypeCode']->__toString();
                $CurrencyCode = $Rate['CurrencyCode']->__toString();

                if (!$InvTypeCode) {
                    throw new Exception('Can not be null ' . __FUNCTION__ . '.RatePlans.RatePlan.Rates.Rate.@RatePlanCode');
                }

                if ($CurrencyCode != "USD") {
                    throw new Exception('Is not valid ' . __FUNCTION__ . '.RatePlans.RatePlan.Rates.Rate.@CurrencyCode:' . $CurrencyCode);
                }

                $roomRatePlans = $this->getAvailableRooms()->first(function ($query, $key) use ($InvTypeCode) {
                    return $query->channels[0]->pivot->code == $InvTypeCode;
                });

                if (empty($roomRatePlans)) {
                    throw new Exception('Looks like InvTypeCode : ' . $InvTypeCode . ', is not valid ' . __FUNCTION__ . '.RatePlans.RatePlan.Rates.Rate.@InvTypeCode');
                }

                $ratePlanRoom = $roomRatePlans->rates_plan_room->first(function ($query, $key) use ($RatePlanCode) {
                    return $query->rate_plan->code == $RatePlanCode;
                });

                if (empty($ratePlanRoom)) {
                    throw new Exception('Looks like RatePlanCode : ' . $RatePlanCode . ', is not valid ' . __FUNCTION__ . '.RatePlans.RatePlan.@RatePlanCode');
                }

                if (!empty($ratePlanRoom->calendarys)) {
                    foreach ($this->getDateRange() as $date) {
                        $dateInput = $date->format("Y-m-d");

                        $inventoriDate = $ratePlanRoom->calendarys->first(function ($query, $key) use ($dateInput) {
                            return $query->date == $dateInput;
                        });

                        if (empty($inventoriDate)) {
                            $ratePlanRoomCalendaryInserts[$ratePlanRoom->id][$dateInput] = [
                                'rates_plans_room_id' => $ratePlanRoom->id,
                                'date' => $dateInput,
                            ];
                        }
                    }
                } else {
                    foreach ($this->getDateRange() as $date) {
                        $dateInput = $date->format("Y-m-d");
                        $ratePlanRoomCalendaryInserts[$ratePlanRoom->id][$dateInput] = [
                            'rates_plans_room_id' => $ratePlanRoom->id,
                            'date' => $dateInput,
                        ];
                    }
                }
            }
        }

        if (count($ratePlanRoomCalendaryInserts) > 0) {
            foreach ($ratePlanRoomCalendaryInserts as $dates) {
                DB::table('rates_plans_calendarys')
                    ->insert($dates);
            }
            $this->setAvailableRooms();
        }

        $inseRatetDates = array();
        $updateRatetDates = array();
        foreach ($this->getXMLRequest()->RatePlans->RatePlan as $RatePlan) {
            $desdeFecha = $RatePlan['Start']->__toString();
            $hastaFecha = $RatePlan['End']->__toString();
            $RatePlanCode = $RatePlan['RatePlanCode']->__toString();

            $this->setDateRange($desdeFecha, $hastaFecha);
            foreach ($RatePlan->Rates->Rate as $Rate) {
                $InvTypeCode = $Rate['InvTypeCode']->__toString();

                $roomRatePlans = $this->getAvailableRooms()->first(function ($query, $key) use ($InvTypeCode) {
                    return $query->channels[0]->pivot->code == $InvTypeCode;
                });

                $ratePlanRoom = $roomRatePlans->rates_plan_room->first(function ($query, $key) use ($RatePlanCode) {
                    return $query->rate_plan->code == $RatePlanCode;
                });

                $rates = array();
                foreach ($Rate->BaseByGuestAmts->BaseByGuestAmt as $BaseByGuestAmt) {
                    if (!empty($BaseByGuestAmt->attributes()->NumberOfGuests)) { // Logica por porsona
                        if ((int)$BaseByGuestAmt->attributes()->AgeQualifyingCode == "10") { /* Adiltos */
                            $rates['adult'][] = [
                                'num_adult' => (int)$BaseByGuestAmt->attributes()->NumberOfGuests,
                                'price_adult' => (float)$BaseByGuestAmt->attributes()->AmountAfterTax,
//                                'price_adult' => (float)$BaseByGuestAmt->attributes()->AmountBeforeTax,
                            ];
                        }
                    } else { // Logica por habitacion
                        $rates['by_room'] = ['price_total' => (float)$BaseByGuestAmt->attributes()->AmountAfterTax];
//                        $rates['by_room'] = ['price_total' => (float)$BaseByGuestAmt->attributes()->AmountBeforeTax];
                    }
                }

                if (isset($Rate->AdditionalGuestAmounts->AdditionalGuestAmount)) {
                    foreach ($Rate->AdditionalGuestAmounts->AdditionalGuestAmount as $BaseByGuestAmt) {
                        if (((int)$BaseByGuestAmt->attributes()->AgeQualifyingCode) == 8
                            or ((int)$BaseByGuestAmt->attributes()->AgeQualifyingCode) == 7) { // Logica por porsona
                            $rates['child'][0] = [
                                'num_child' => 1,
                                'price_child' => (float)$BaseByGuestAmt->attributes()->Amount,
                            ];
                        } else { // Logica por habitacion
                            $rates['extra'][0] = ['price_extra' => (float)$BaseByGuestAmt->attributes()->Amount];
                        }
                    }
                }

                $ratePlanRoomCalendarys = $this->getRatePlanRoomRates($ratePlanRoom, ['Start' => $desdeFecha, 'End' => $hastaFecha]);
                foreach ($this->getDateRange() as $date) {
                    $dateInput = $date->format("Y-m-d");

                    $calendaryRates = $ratePlanRoomCalendarys->first(function ($query, $key) use ($dateInput) {
                        return $query->date == $dateInput;
                    });
                    foreach ($rates as $rateType => $ratesType) {
                        foreach ($ratesType as $rate) {
                            if ($calendaryRates->rate->count() > 0) {
                                $rateSelected = $calendaryRates->rate->first(function ($value, $key) use ($rateType, $rate) {
                                    if ($rateType == 'adult') {
                                        return $value->num_adult == $rate['num_adult'];
                                    } elseif ($rateType == 'child') {
                                        return $value->num_child == $rate['num_child'];
                                    } elseif ($rateType == 'extra') {
                                        return $value->price_extra != null and $value->price_extra > 0;
                                    } elseif ($rateType == 'by_room') {
                                        return $value->price_total != null and $value->price_total > 0;
                                    }
                                    return false;
                                });
                            } else {
                                $rateSelected = null;
                            }

                            if (empty($rateSelected)) {
                                $rate['rates_plans_calendarys_id'] = $calendaryRates->id;
                                if ($rateType == 'adult') {
                                    $inseRatetDates[$rateType][$rate['num_adult'] . '|' . $calendaryRates->id] = $rate;
                                } else {
                                    $inseRatetDates[$rateType][$calendaryRates->id] = $rate;
                                }
                            } else {
                                $updateRatetDates[$rateSelected->id] = array_merge($rateSelected->toArray(), $rate);
                            }
                        }
                    }
                }
            }
        }
        if (count($inseRatetDates) > 0) {
            foreach ($inseRatetDates as $inseRatetDate) {
                DB::table('rates')
                    ->insert($inseRatetDate);
            }
        }

        if (count($updateRatetDates) > 0) {
            DB::table('rates')->insertOnDuplicateKey(
                $updateRatetDates,
                [
                    'num_adult',
                    'num_child',
                    'num_infant',
                    'price_adult',
                    'price_child',
                    'price_infant',
                    'price_extra',
                    'price_total',
                ]
            );
        }

        $xml = $this->setXMLResponse();
        $xml->addChild('Success');

        return $xml;
    }

    /**
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelRatePlanRQ()
    {
        $xml = $this->setXMLResponse();
        $xml->addChild('Success');
        foreach ($this->getXMLRequest()->RatePlans->RatePlan as $RatePlan) {
            $this->setStartEndDates($RatePlan->DateRange);

            $this->setRequestedHotel($RatePlan->HotelRef);
            $this->setAvailableRatesPlans();

            $RatePlans = $xml->addChild("RatePlans");
            foreach ($RatePlan->RatePlanCandidates->RatePlanCandidate as $RatePlanCandidate) {
                $RatePlanCode = $RatePlanCandidate['RatePlanCode']->__toString();
                $RequestRatePlan = $this->getAvailableRatesPlans()->first(function ($query, $key) use ($RatePlanCode) {
                    return $query->code == $RatePlanCode;
                });

                if (empty($RequestRatePlan)) {
                    throw new Exception('Looks like RatePlanCode : ' . $RatePlanCode . ', is not valid ' . __FUNCTION__ . '.RatePlans.RatePlan.@RatePlanCode');
                }

                foreach ($RequestRatePlan->rooms as $RatePlanRoom) {
                    $InvTypeCode = $RatePlanRoom->channels->first()->pivot->code;

                    $RateRoom = $RatePlanRoom->rates_plan_room()
                        ->where('rates_plans_id', '=', $RequestRatePlan->id)
                        ->where('channel_id', '=', $this->channelId())
                        ->first();

                    $RateRoomCalendary = $RateRoom->calendarys()
                        ->whereBetween('date', [$this->getStartDate(), $this->getEndDate()])
                        ->get();
                    foreach ($this->getDateRange() as $date) {
                        $dateInput = $date->format("Y-m-d");
                        $calendaryDate = $RateRoomCalendary->first(function ($query) use ($dateInput) {
                            return $query->date == $dateInput;
                        });

                        if (empty($calendaryDate)) {
                            continue;
                        }

                        $dateRates = $calendaryDate->rate()->get();
                        $AdditionalGuestAmount = null;
                        $Rate = null;
                        foreach ($dateRates as $dateRate) {
                            if (empty($Rate)) {
                                $RatePlan = $RatePlans->addChild("RatePlan");
                                $RatePlan->addAttribute('RatePlanCode', $RatePlanCode);
                                $RatePlan->addAttribute('Start', $dateInput);
                                $RatePlan->addAttribute('End', $dateInput);

                                $Rate = $RatePlan->addChild("Rates")->addChild("Rate");
                                $Rate->addAttribute('InvTypeCode', $InvTypeCode);
                            }

                            $BaseByGuestAmts = $Rate->addChild('BaseByGuestAmts');
                            if ($dateRate->price_total != null) {
                                $BaseByGuestAmt = $BaseByGuestAmts->addChild('BaseByGuestAmt');
                                $BaseByGuestAmt->addAttribute('AmountAfterTax', priceRound($dateRate->price_total));
//                                $BaseByGuestAmt->addAttribute('AmountBeforeTax', priceRound($dateRate->price_total));
                                $BaseByGuestAmt->addAttribute('AgeQualifyingCode', 10);
                            }

                            if ($dateRate->num_adult != null and $dateRate->num_adult > 0) {
                                $BaseByGuestAmt = $BaseByGuestAmts->addChild('BaseByGuestAmt');

                                $BaseByGuestAmt->addAttribute('AmountAfterTax', priceRound($dateRate->price_adult));
//                                $BaseByGuestAmt->addAttribute('AmountBeforeTax', priceRound($dateRate->price_adult));
                                $BaseByGuestAmt->addAttribute('NumberOfGuests', $dateRate->num_adult);
                                $BaseByGuestAmt->addAttribute('AgeQualifyingCode', 10);
                            }

                            if ($dateRate->price_extra != null) {
                                if (empty($AdditionalGuestAmount)) {
                                    $AdditionalGuestAmounts = $Rate->addChild('AdditionalGuestAmounts');
                                }
                                $AdditionalGuestAmount = $AdditionalGuestAmounts->addChild('AdditionalGuestAmount');
                                $AdditionalGuestAmount->addAttribute('Amount', priceRound($dateRate->price_extra));
                                $AdditionalGuestAmount->addAttribute('AgeQualifyingCode', 10);
                            }

//                            if ($this->hotel()->allows_child or $this->hotel()->allows_teenagers) {
                            if (empty($AdditionalGuestAmount)) {
                                $AdditionalGuestAmounts = $Rate->addChild('AdditionalGuestAmounts');
                            }

                            if ($dateRate->num_child != null) {
                                $AdditionalGuestAmount = $AdditionalGuestAmounts->addChild('AdditionalGuestAmount');
                                $AdditionalGuestAmount->addAttribute('Amount', priceRound($dateRate->price_child));
                                $AdditionalGuestAmount->addAttribute('AgeQualifyingCode', 8);
                            }

                            if ($dateRate->num_infant != null) {
                                $AdditionalGuestAmount = $AdditionalGuestAmounts->addChild('AdditionalGuestAmount');
                                $AdditionalGuestAmount->addAttribute('Amount', priceRound($dateRate->price_infant));
                                $AdditionalGuestAmount->addAttribute('AgeQualifyingCode', 8);
                            }
//                            }
                        }
                    }
                }
            }
        }

        return $xml;
    }

    /**
     *
     * Room Availability updates.
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelAvailNotifRQ()
    {
        $this->setRequestedHotel($this->getXMLRequest()->AvailStatusMessages);
        $this->setAvailableRooms();

        $slqUpdate = array();
        $slqInsert = array();
        foreach ($this->getXMLRequest()->AvailStatusMessages->AvailStatusMessage as $AvailStatusMessage) {
            $InvTypeCode = $AvailStatusMessage->StatusApplicationControl['InvTypeCode']->__toString();
            $RatePlanCode = $AvailStatusMessage->StatusApplicationControl['RatePlanCode']->__toString();

            if (!$InvTypeCode) {
                throw new Exception('Can not be null ' . __FUNCTION__ . '.AvailStatusMessages.StatusApplicationControl.@InvTypeCode');
            }
            if (!$RatePlanCode) {
                throw new Exception('Can not be null ' . __FUNCTION__ . '.AvailStatusMessages.StatusApplicationControl.@RatePlanCode');
            }

            $Room = $this->getAvailableRooms()->first(function ($query, $key) use ($InvTypeCode) {
                return $query->channels[0]->pivot->code == $InvTypeCode;
            });

            if (empty($Room)) {
                throw new Exception('Looks like InvTypeCode : ' . $InvTypeCode . ', is not valid ' . __FUNCTION__ . '.AvailStatusMessages.StatusApplicationControl.@InvTypeCode');
            }

            $RatePlanRoom = $Room->rates_plan_room->first(function ($query, $key) use ($RatePlanCode) {
                return $query->rate_plan->code == $RatePlanCode;
            });

            if (empty($RatePlanRoom)) {
                throw new Exception('Looks like RatePlanCode : ' . $RatePlanCode . ', is not valid ' . __FUNCTION__ . '.AvailStatusMessages.StatusApplicationControl.@RatePlanCode');
            }

//            $RatePlan = $RatePlanRoom->rate_plan;

            $messages = [];
            if (isset($AvailStatusMessage->LengthsOfStay)) {
                if ($AvailStatusMessage->LengthsOfStay['ArrivalDateBased']) {
                    switch ($AvailStatusMessage->LengthsOfStay['ArrivalDateBased']->__toString()) {
                        case 'true':
                            $ArrivalDateBased = true;
                            break;
                        case 'false':
                            $ArrivalDateBased = false;
                            break;
                        default:
                            throw new Exception('Is not valid ' . __FUNCTION__ . '.AvailStatusMessages.AvailStatusMessages.LengthsOfStay.ArrivalDateBased');
                            break;
                    }
                }

                if (!isset($AvailStatusMessage->LengthsOfStay->LengthOfStay)) {
                    throw new Exception('LengthsOfStay.LengthOfStay', self::REQUIRED_FIELD_MISSING);
                }

                foreach ($AvailStatusMessage->LengthsOfStay->LengthOfStay as $LengthOfStay) {
                    $MinMaxMessageType = $LengthOfStay->attributes()->MinMaxMessageType->__toString();
                    if ($MinMaxMessageType == "MinLOS") {
                        $messages['restrictions']['min_length_stay'] = empty($LengthOfStay->attributes()->Time) ? 0 : (int)$LengthOfStay->attributes()->Time;
                    } elseif ($MinMaxMessageType == "MaxLOS") {
                        $messages['restrictions']['max_length_stay'] = empty($LengthOfStay->attributes()->Time) ? 99 : (int)$LengthOfStay->attributes()->Time;
                    }
                }
            }

            if (isset($AvailStatusMessage->RestrictionStatus)) {
                foreach ($AvailStatusMessage->RestrictionStatus as $RestrictionStatus) {
                    if (isset($RestrictionStatus->attributes()->Restriction)) {
                        $restriction = strtolower($AvailStatusMessage->RestrictionStatus->attributes()->Restriction->__toString());
                        if (!isset($AvailStatusMessage->RestrictionStatus->attributes()->Status)) {
                            throw new Exception('RestrictionStatus.Status', self::REQUIRED_FIELD_MISSING);
                        }
                        $restrictionStatus = (strtolower($AvailStatusMessage->RestrictionStatus->attributes()->Status->__toString())) == 'open' ? 0 : 1;
                        switch ($restriction) {
                            case 'arrival':
                                $messages['inventories']['lockedArrival'] = $restrictionStatus;
                                break;
                            case 'departure':
                                $messages['inventories']['lockedDeparture'] = $restrictionStatus;
                                break;
                            case 'master':
                                $messages['inventories']['locked'] = $restrictionStatus;
                                break;
                            default:
                                throw new Exception('Is not valid ' . __FUNCTION__ . '.AvailStatusMessages.AvailStatusMessage.RestrictionStatus[@Restriction]');
                                break;
                        }
                    }

                    if (isset($RestrictionStatus->attributes()->MinAdvancedBookingOffset)) {
                        $messages['restrictions']['min_ab_offset'] = (new DateInterval($RestrictionStatus->attributes()->MinAdvancedBookingOffset->__toString()))->format('%d');
                    }

                    if (isset($RestrictionStatus->attributes()->MaxAdvancedBookingOffset)) {
                        $messages['restrictions']['max_ab_offset'] = (new DateInterval($RestrictionStatus->attributes()->MaxAdvancedBookingOffset->__toString()))->format('%d');
                    }
                }
            }

            if (count($messages) == 0) {
                continue;
            }

            $this->setStartEndDates($AvailStatusMessage->StatusApplicationControl);
            $RateRoomInventory = $this->getRatePlanRoomInventories($RatePlanRoom, ['Start' => $this->getStartDate(), 'End' => $this->getEndDate()]);
            $RateRoomCalendary = $this->getRatePlanRoomRates($RatePlanRoom, ['Start' => $this->getStartDate(), 'End' => $this->getEndDate()]);

            $insertInventary = array();
            $updateInventary = array();
            $insertCalendary = array();
            $updateCalendary = array();
            foreach ($this->getDateRange() as $date) {
                $dateInput = $date->format("Y-m-d");
                $tokenDate = $dateInput . '|' . $RatePlanRoom->id;
                if (isset($messages['inventories'])) {
                    $nvenrotyDate = null;
                    if (!empty($RateRoomInventory)) {
                        $invDate = $RateRoomInventory->first(function ($value, $key) use ($dateInput) {
                            return $value['date'] == $dateInput;
                        });
                    }

                    if (empty($invDate)) { // create
                        $insertInventary[$tokenDate] = [
                            'rate_plan_rooms_id' => $RatePlanRoom->id,
                            'date' => $dateInput,
                            'day' => substr($dateInput, -2),
                            'inventory_num' => empty($messages['inventories']['inventory_num']) ? 0 : $messages['inventories']['inventory_num'],
                            'locked' => empty($messages['inventories']['locked']) ? 0 : $messages['inventories']['locked'],
                        ];
                    } else {
                        $updateInventary[$tokenDate] = $invDate->toArray();

                        if (!empty($messages['inventories']['inventory_num'])) {
                            $updateInventary[$tokenDate]['inventory_num'] = $messages['inventories']['inventory_num'];
                        }

                        if (isset($messages['inventories']['locked'])) {
                            $updateInventary[$tokenDate]['locked'] = $messages['inventories']['locked'];
                        }
                    }
                }

                if (isset($messages['restrictions'])) {
                    $calendaryDate = null;

                    if (!empty($RateRoomCalendary)) {
                        $calDate = $RateRoomCalendary->first(function ($value, $key) use ($dateInput) {
                            return $value->date == $dateInput;
                        });
                    }

                    if (empty($calDate)) { // create
                        $insertCalendary[$tokenDate] = [
                            'rates_plans_room_id' => $RatePlanRoom->id,
                            'date' => $dateInput,
                            'min_length_stay' => empty($messages['restrictions']['min_length_stay']) ? 1 : $messages['restrictions']['min_length_stay'],
                            'max_length_stay' => empty($messages['restrictions']['max_length_stay']) ? 99 : $messages['restrictions']['max_length_stay'],
                            'min_ab_offset' => empty($messages['restrictions']['min_ab_offset']) ? 1 : $messages['restrictions']['min_ab_offset'],
                            'max_ab_offset' => empty($messages['restrictions']['max_ab_offset']) ? 365 : $messages['restrictions']['max_ab_offset'],
                        ];
                    } else {
                        $updateCalendary[$tokenDate] = $calDate->toArray();

                        unset($updateCalendary[$tokenDate]['rate_plan_rooms_id']);
                        unset($updateCalendary[$tokenDate]['rate']);
                        $updateCalendary[$tokenDate]['rates_plans_room_id'] = $RatePlanRoom->id;

                        if (!empty($messages['restrictions']['min_length_stay'])) {
                            $updateCalendary[$tokenDate]['min_length_stay'] = $messages['restrictions']['min_length_stay'];
                        }
                        if (!empty($messages['restrictions']['max_length_stay'])) {
                            $updateCalendary[$tokenDate]['max_length_stay'] = $messages['restrictions']['max_length_stay'];
                        }
                        if (!empty($messages['restrictions']['min_ab_offset'])) {
                            $updateCalendary[$tokenDate]['min_ab_offset'] = $messages['restrictions']['min_ab_offset'];
                        }
                        if (!empty($messages['restrictions']['max_ab_offset'])) {
                            $updateCalendary[$tokenDate]['max_ab_offset'] = $messages['restrictions']['max_ab_offset'];
                        }
                    }
                }
            }

            if (count($insertInventary) > 0) {
                DB::table('inventories')
                    ->insert($insertInventary);
            }
            if (count($updateInventary) > 0) {
                DB::table('inventories')
                    ->insertOnDuplicateKey($updateInventary, [
                        'inventory_num',
                        'locked'
                    ]);
            }

            if (count($insertCalendary) > 0) {
                DB::table('rates_plans_calendarys')
                    ->insert($insertCalendary);
            }
            if (count($updateCalendary) > 0) {
                DB::table('rates_plans_calendarys')
                    ->insertOnDuplicateKey($updateCalendary, [
                        'min_length_stay',
                        'max_length_stay',
                        'min_ab_offset',
                        'max_ab_offset'
                    ]);
            }
        }

        $xml = $this->setXMLResponse();
        $xml->addChild('Success');
        return $xml;
    }

    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelAvailGetRQ()
    {
        $xml = $this->setXMLResponse();
        $xml->addChild('Success');
        foreach ($this->getXMLRequest()->HotelAvailRequests->HotelAvailRequest as $value) {
            $this->setRequestedHotel($value->HotelRef);
            $this->setAvailableRooms();

            $this->setStartEndDates($value->DateRange);

            $AvailStatusMessages = $xml->addChild('AvailStatusMessages');
            $AvailStatusMessages->addAttribute('HotelCode', $this->getHotelCode());
            foreach ($value->RoomTypeCandidates->RoomTypeCandidate as $RoomTypeCandidate) {
                $InvTypeCode = $RoomTypeCandidate['RoomTypeCode']->__toString();

                if (!$InvTypeCode) {
                    throw new Exception('Can not be null ' . __FUNCTION__ . 'HotelAvailRequests.HotelAvailRequest.RoomTypeCandidates.RoomTypeCandidate.@InvTypeCode');
                }

                $Room = $this->getAvailableRooms()->first(function ($query, $key) use ($InvTypeCode) {
                    return $query->channels->first()->pivot->code == $InvTypeCode;
                });

                if (empty($Room)) {
                    throw new Exception('Looks like InvTypeCode : ' . $InvTypeCode . ', is not valid ' . __FUNCTION__ . 'HotelAvailRequests.HotelAvailRequest.RoomTypeCandidates.RoomTypeCandidate.@InvTypeCode');
                }

                $availData = array();
                foreach ($value->RatePlanCandidates->RatePlanCandidate as $RatePlanCandidate) {
                    $RatePlanCode = $RatePlanCandidate['RatePlanCode']->__toString();
                    if (!$RatePlanCode) {
                        throw new Exception('Can not be null ' . __FUNCTION__ . 'HotelAvailRequests.HotelAvailRequest.RatePlanCandidates->RatePlanCandidate.@RatePlanCode');
                    }

                    $RatePlanRoom = $Room->rates_plan_room->first(function ($query, $key) use ($RatePlanCode) {
                        return $query->rate_plan->code == $RatePlanCode;
                    });

                    if (empty($RatePlanRoom)) {
                        throw new Exception('Looks like RatePlanCode : ' . $RatePlanCode . ', is not valid ' . __FUNCTION__ . 'HotelAvailRequests.HotelAvailRequest.RatePlanCandidates->RatePlanCandidate.@RatePlanCode');
                    }

                    $RatePlan = $RatePlanRoom->rate_plan;

                    $RateRoomInventory = $this->getRatePlanRoomInventories($RatePlanRoom, ['Start' => $this->getStartDate(), 'End' => $this->getEndDate()]);
                    $RateRoomCalendary = $this->getRatePlanRoomRates($RatePlanRoom, ['Start' => $this->getStartDate(), 'End' => $this->getEndDate()]);

                    $availData[$RatePlan->id]['codigoTarifa'] = $RatePlanCode;
                    $availData[$RatePlan->id]['habitacionIdManual'] = $InvTypeCode;
                    $availData[$RatePlan->id]['avail']['inv'] = $RateRoomInventory;
                    $availData[$RatePlan->id]['avail']['cal'] = $RateRoomCalendary;
                }

                $AvailStatusMessage = array();
                foreach ($availData as $avails) {
                    foreach ($this->getDateRange() as $date) {
                        $dateInput = $date->format("Y-m-d");

                        $dateIntentory = $avails['avail']['inv']->first(function ($query) use ($dateInput) {
                            return $query->date == $dateInput;
                        });

                        $dateCalendary = $avails['avail']['cal']->first(function ($query) use ($dateInput) {
                            return $query->date == $dateInput;
                        });

                        $AvailStatusMessage = $AvailStatusMessages->addChild('AvailStatusMessage');

                        $BookingLimit = 0;
                        $AvailStatus = 0;
                        if (!empty($dateIntentory)) {
                            $BookingLimit = empty($dateIntentory->inventory_num) ? $BookingLimit : $dateIntentory->inventory_num;
                            $AvailStatus = empty($dateIntentory->locked) ? $AvailStatus : 1;
                        }

                        $MinAdvancedBookingOffset = 1;
                        $MinLOS = 1;
                        $MaxLOS = 99;
                        if (!empty($dateCalendary)) {
                            $MinAdvancedBookingOffset = empty($dateCalendary->min_ab_offset) ? $MinAdvancedBookingOffset : $dateCalendary->min_ab_offset;
                            $MinLOS = empty($dateCalendary->min_length_stay) ? $MinLOS : $dateCalendary->min_length_stay;
                            $MaxLOS = empty($dateCalendary->max_length_stay) ? $MaxLOS : $dateCalendary->max_length_stay;
                        }


                        $AvailStatusMessage->addAttribute('BookingLimit', $BookingLimit);
                        $AvailStatusMessage->addAttribute('BookingLimitMessageType', 'SetLimit');

                        $StatusApplicationControl = $AvailStatusMessage->addChild('StatusApplicationControl');
                        $StatusApplicationControl->addAttribute('Start', $dateInput);
                        $StatusApplicationControl->addAttribute('End', $dateInput);
                        $StatusApplicationControl->addAttribute('RatePlanCode', $avails['codigoTarifa']);
                        $StatusApplicationControl->addAttribute('InvTypeCode', $avails['habitacionIdManual']);

                        $LengthsOfStay = $AvailStatusMessage->addChild('LengthsOfStay');
                        $LengthsOfStay->addAttribute('ArrivalDateBased','true');

                        $LengthOfStayMin = $LengthsOfStay->addChild('LengthOfStay');
                        $LengthOfStayMin->addAttribute('Time', $MinLOS);
                        $LengthOfStayMin->addAttribute('TimeUnit', 'Day');
                        $LengthOfStayMin->addAttribute('MinMaxMessageType', 'MinLOS');

                        $LengthOfStayMax = $LengthsOfStay->addChild('LengthOfStay');
                        $LengthOfStayMax->addAttribute('Time', $MaxLOS);
                        $LengthOfStayMax->addAttribute('TimeUnit', 'Day');
                        $LengthOfStayMax->addAttribute('MinMaxMessageType', 'MaxLOS');

                        $RestrictionStatus = $AvailStatusMessage->addChild('RestrictionStatus');
                        $RestrictionStatus->addAttribute('Restriction', 'Arrival');
                        $RestrictionStatus->addAttribute('Status', $AvailStatus ? 'Close' : 'Open');

                        $RestrictionStatus2 = $AvailStatusMessage->addChild('RestrictionStatus');
                        $RestrictionStatus2->addAttribute('Restriction', 'Departure');
                        $RestrictionStatus2->addAttribute('Status', $AvailStatus ? 'Close' : 'Open');

                        $RestrictionStatus3 = $AvailStatusMessage->addChild('RestrictionStatus');
                        $RestrictionStatus3->addAttribute('Restriction', 'Master');
                        $RestrictionStatus3->addAttribute('Status', $AvailStatus ? 'Close' : 'Open');

                        $RestrictionStatus4 = $AvailStatusMessage->addChild('RestrictionStatus');
                        $RestrictionStatus4->addAttribute('MinAdvancedBookingOffset', 'P' . $MinAdvancedBookingOffset . 'D');
                        $RestrictionStatus4->addAttribute('MaxAdvancedBookingOffset', 'P365D');
                    }
                }
            }
        }

        return $xml;
    }

    /**
     * INVENTORI updates.
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelInvCountRQ()
    {
        $xml = $this->setXMLResponse();
        $xml->addChild('Success');
        foreach ($this->getXMLRequest()->HotelInvCountRequests->HotelInvCountRequest as $HotelInvCountRequest) {
            $this->setRequestedHotel($HotelInvCountRequest->HotelRef);
            $this->setAvailableRooms();
            $this->setStartEndDates($HotelInvCountRequest->DateRange);

            $Inventories = $xml->addChild("Inventories");
            $Inventories->addAttribute("HotelCode", $this->getHotelCode());
            foreach ($HotelInvCountRequest->RoomTypeCandidates->RoomTypeCandidate as $RoomTypeCandidate) {
                $InvTypeCode = $RoomTypeCandidate['RoomTypeCode']->__toString();

                if (!$InvTypeCode) {
                    throw new Exception('Can not be null ' . __FUNCTION__ . '.HotelInvCountRequests.HotelInvCountRequest.RoomTypeCandidates.RoomTypeCandidate.@InvTypeCode');
                }

                $Room = $this->getAvailableRooms()->first(function ($query, $key) use ($InvTypeCode) {
                    return $query->channels->first()->pivot->code == $InvTypeCode;
                });

                if (empty($Room)) {
                    throw new Exception('Looks like InvTypeCode : ' . $InvTypeCode . ', is not valid ' . __FUNCTION__ . 'HotelInvCountRequests.HotelInvCountRequest.RoomTypeCandidates.RoomTypeCandidate.@InvTypeCode');
                }

                $RatePlanRooms = $Room->rates_plan_room;
                foreach ($RatePlanRooms as $RatePlanRoom) {
                    $RatePlan = $RatePlanRoom->rate_plan;

                    $RateRoomInventory = $this->getRatePlanRoomInventories($RatePlanRoom, ['Start' => $this->getStartDate(), 'End' => $this->getEndDate()]);
                    foreach ($RateRoomInventory as $dip) {
                        $Inventory = $Inventories->addChild('Inventory');
                        $StatusApplicationControl = $Inventory->addChild('StatusApplicationControl');
                        $StatusApplicationControl->addAttribute('Start', $dip->date);
                        $StatusApplicationControl->addAttribute('End', $dip->date);
                        $StatusApplicationControl->addAttribute('InvTypeCode', $InvTypeCode);
                        $StatusApplicationControl->addAttribute('RatePlanCode', $RatePlan->code);
//                    $StatusApplicationControl->addAttribute('InvBlockCodeApply', 'BlockCode');
//                    $StatusApplicationControl->addAttribute('InvBlockCodeApply', 'BlockCode');
//                    $StatusApplicationControl->addAttribute('InvBlockCode', 'GRP123');
                        $InvCounts = $Inventory->addChild('InvCounts');
                        $InvCount = $InvCounts->addChild('InvCount');
                        $InvCount->addAttribute('CountType', '2');
                        $InvCount->addAttribute('ActionType', 'Remaining');
                        $InvCount->addAttribute('Count', $dip->inventory_num ?? 0);
                    }
                }
            }
        }

        return $xml;
    }

}
