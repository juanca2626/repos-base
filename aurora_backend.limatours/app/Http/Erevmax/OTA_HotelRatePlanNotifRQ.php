<?php


namespace App\Http\Erevmax;


use App\Channel;
use App\Http\Erevmax\Traits\Erevmax as ErevmaxTrait;
use App\RatesPlansCalendarys;
use App\RatesPlansRooms;
use DateTime;
use Exception;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class OTA_HotelRatePlanNotifRQ
{
    use ErevmaxTrait;

    /** @var SimpleXMLElement */
    public $request;
    /** @var SimpleXMLElement */
    public $response;

    /**
     * OTA_HotelAvailNotifRQ constructor.
     * @param SimpleXMLElement $simpleXMLElement
     * @param Channel|null $channel
     * @throws Exception
     */
    public function __construct(SimpleXMLElement $simpleXMLElement, Channel $channel = null)
    {
        $this->setChannel($channel);

        $this->request = $simpleXMLElement;
        $this->setRequestedHotel($this->getHotelRef());

        $this->setDataFilters();
        $this->ratesPlansRooms($this->hotel(), $withCalendarys = true, $withInventory = false);
    }

    /**
     * @return SimpleXMLElement
     */
    public function getHotelRef()
    {
        return $this->data()->RatePlans;
    }

    /**
     * @return SimpleXMLElement
     */
    public function data()
    {
        return $this->request->OTA_HotelRatePlanNotifRQ;
    }

    /**
     * Extrae toso los codigos de tarifa y habitacion solicitadon en el xml para filtarr las consultas a la base de datos
     * en base a estos codigos
     *
     * Tambien estrae toda las fechas de inicio y fin (Start/End) y las ordeta para obtener la fecha mas baja y la fecha
     * mas alta para filtar las consultas a la base de datos
     *
     * @throws Exception
     */
    public function setDataFilters()
    {
        $this->filters = collect([
            'start_date' => collect(),
            'end_date' => collect(),
            'rates_plans_codes' => collect(),
            'rooms_codes' => collect()
        ]);

        foreach ($this->data()->RatePlans->RatePlan as $RatePlan) {
            $RatePlanNotifType = strtolower($RatePlan->attributes()->RatePlanNotifType->__toString());
            if (!in_array($RatePlanNotifType, ['new', 'delta'])) {
                continue;
            }

            if (empty($RatePlan->attributes()->RatePlanCode)) {
                throw new Exception('RatePlans.RatePlan.RatePlanCode can not null or empty', $this->REQUIRED_FIELD_MISSING);
            }
            $this->filters['rates_plans_codes']->add($RatePlan->attributes()->RatePlanCode->__toString());
            foreach ($RatePlan->Rates->Rate as $Rate) {
                if (empty($Rate->attributes()->InvTypeCode)) {
                    throw new Exception('RatePlans.RatePlan.Rates.Rate.InvTypeCode can not null or empty', $this->REQUIRED_FIELD_MISSING);
                }
                $this->filters['rooms_codes']->add($Rate->attributes()->InvTypeCode->__toString());

                $DateRange = $Rate->attributes();
                if (empty($DateRange->Start) or empty($DateRange->End)) {
                    throw new Exception('Start and End date can not null or empty', $this->REQUIRED_FIELD_MISSING);
                }

                $this->filters['start_date']->add($DateRange->Start->__toString());
                $this->filters['end_date']->add($DateRange->End->__toString());
            }
        }

        $this->getFilters()->transform(function (Collection $item, $key) {
            if ($key == 'start_date') {
                $item = $item->sort()->first();
            }
            if ($key == 'end_date') {
                $item = $item->sort()->last();
            }

            if ($key == 'rates_plans_codes' or $key == 'rooms_codes') {
                $item = $item->unique();
            }

            return $item;
        });
    }

    /**
     * @param SimpleXMLElement $simpleXMLElement
     * @return
     * @throws Exception
     */
    public function process()
    {
        $requestedRoomRates = array();
        foreach ($this->data()->RatePlans->RatePlan as $RatePlan) {
            $RatePlanNotifType = strtolower($RatePlan->attributes()->RatePlanNotifType->__toString());
            $RatePlanCode = (string)$RatePlan->attributes()->RatePlanCode->__toString();

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
                        throw new Exception('Start date cannot by higer than End date', $this->ROOM_OR_RATE_NOT_FOUND);
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

                    if (!$this->getRatesPlansRooms()->offsetExists($RatePlanCode)) {
                        throw new Exception('Relation RatePlan="' . $RatePlanCode . '"" and InvTypeCode="' . $RooomCode . '"  not found', $this->ROOM_OR_RATE_NOT_FOUND);
                    }

                    if (!$this->getRatesPlansRooms()->offsetGet($RatePlanCode)->contains('room.channel_room.code', $InvTypeCode)) {
                        throw new Exception('Relation RatePlan="' . $RatePlanCode . '"" and InvTypeCode="' . $RooomCode . '"  not found', $this->ROOM_OR_RATE_NOT_FOUND);
                    }

                    $this->getRatesPlansRooms()->offsetGet($RatePlanCode)->each(function (RatesPlansRooms $RatePlanRoom, $key) use ($InvTypeCode, $requestedRoomRateDates) {
                        if ($InvTypeCode == $RatePlanRoom->room->channel_room->code) {
                            foreach ($requestedRoomRateDates['DateRanges'] as $DateRanges => $rates) {
                                list($startDate, $endDate) = explode('|', $DateRanges);
                                $this->setDateRange($startDate, $endDate);
                                foreach ($this->getDateRange() as $date) {
                                    /** @var DateTime $date */
                                    $dateInput = $date->format("Y-m-d");

                                    $dateRates = $RatePlanRoom->getCalendaryDate($dateInput);
                                    if (empty($dateRates)) { // create
                                        $dateRates = $RatePlanRoom->addCalendaryDate($dateInput);
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
                        }
                    });
                }
            }
        }

        $this->getRatesPlansRooms()->each(function (Collection $Collection) {
            $Collection->each(function (RatesPlansRooms $RatesPlansRoom) {
                $RatesPlansRoom->calendarys()->saveMany($RatesPlansRoom->calendarys);
                $RatesPlansRoom->calendarys->each(function (RatesPlansCalendarys $calendary) {
                    $calendary->rate()->saveMany($calendary->rate);
                });
            });
        });
    }
}
