<?php


namespace App\Http\Siteminder;

use App\Channel;
use App\Http\Siteminder\Traits\Siteminder as SiteminderTrait;
use App\Language;
use App\RatesPlansCalendarys;
use App\RatesPlansRooms;
use App\Http\Traits\Translations;
use App\Translation;
use DateTime;
use Exception;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class OTA_HotelRateAmountNotifRQ
{
    use SiteminderTrait;

    /** @var SimpleXMLElement */
    public $request;
    /** @var SimpleXMLElement */
    public $response;
    /** @var Translation */
    private $_lang;

    /**
     * OTA_HotelAvailNotifRQ constructor.
     * @param SimpleXMLElement $simpleXMLElement
     * @param Channel|null $channel
     * @throws \Exception
     */
    public function __construct(SimpleXMLElement $simpleXMLElement, Channel $channel = null)
    {
        $this->setChannel($channel);

        $this->request = $simpleXMLElement;
        $this->setRequestedHotel($this->getHotelRef());

        $this->setDataFilters();
        $this->ratesPlansRooms($this->hotel(), $withCalendarys = true, $withInventory = true);
    }

    /**
     * @return SimpleXMLElement
     */
    public function getHotelRef()
    {
        return $this->data();
    }

    /**
     * @return SimpleXMLElement
     */
    public function data()
    {
        return $this->request->OTA_HotelRateAmountNotifRQ->RateAmountMessages;
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
            'rooms_codes' => collect(),
        ]);

        foreach ($this->data()->RateAmountMessage as $RateAmountMessage) {
            if (!isset($RateAmountMessage->StatusApplicationControl)) {
                throw new Exception('Requiered node missing OTA_HotelRateAmountNotifRQ.RateAmountMessages.RateAmountMessage.StatusApplicationControl');
            }

            $attrs = $RateAmountMessage->StatusApplicationControl->attributes();
            if (empty($attrs->Start) or empty($attrs->End)) {
                throw new Exception('Start and End date can not null or empty', $this->REQUIRED_FIELD_MISSING);
            }

            $this->filters['start_date']->add($attrs->Start->__toString());
            $this->filters['end_date']->add($attrs->End->__toString());


            if (empty($attrs->RatePlanCode)) {
                throw new Exception('StatusApplicationControl.RatePlanCode can not null or empty', $this->REQUIRED_FIELD_MISSING);
            }
            $this->filters['rates_plans_codes']->add($attrs->RatePlanCode->__toString());

            if (empty($attrs->InvTypeCode)) {
                throw new Exception('StatusApplicationControl.InvTypeCode can not null or empty', $this->REQUIRED_FIELD_MISSING);
            }

            $this->filters['rooms_codes']->add($attrs->InvTypeCode->__toString());
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
     * @throws Exception
     */
    public function process()
    {
        foreach ($this->data()->RateAmountMessage as $RateAmountMessage) {
            $attrs = $RateAmountMessage->StatusApplicationControl->attributes();
            $avail_message = [
                'start_date' => $attrs->Start->__toString(),
                'end_date' => $attrs->End->__toString(),
                'rate_plan_code' => $attrs->RatePlanCode->__toString(),
                'room_code' => $attrs->InvTypeCode->__toString(),
            ];

            if (!isset($RateAmountMessage->Rates->Rate)) {
                continue;
            }

            $rates = array();
            if (isset($RateAmountMessage->Rates->Rate->BaseByGuestAmts->BaseByGuestAmt)) {
                foreach ($RateAmountMessage->Rates->Rate->BaseByGuestAmts->BaseByGuestAmt as $BaseByGuestAmt) {
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

            if (isset($RateAmountMessage->Rates->Rate->AdditionalGuestAmounts->AdditionalGuestAmount)) {
                foreach ($RateAmountMessage->Rates->Rate->AdditionalGuestAmounts->AdditionalGuestAmount as $BaseByGuestAmt) {
                    if (((int)$BaseByGuestAmt->attributes()->AgeQualifyingCode) == 10) { // adulto extra
                        $rates['price_extra'][] = ['price_extra' => (float)$BaseByGuestAmt->attributes()->Amount];
//                    } else if (((int)$BaseByGuestAmt->attributes()->AgeQualifyingCode) == 8) { // niño extra
//                        $rates['price_extra_child'][] = [
//                            'num_child' => 1,
//                            'price_extra_child' => (float)$BaseByGuestAmt->attributes()->Amount,
//                        ];
                    }
                }
            }

            if (isset($RateAmountMessage->Rates->Rate->RateDescription) and isset($RateAmountMessage->Rates->Rate->RateDescription->Text)) {
                $rates['dates_description'] = $RateAmountMessage->Rates->Rate->RateDescription->Text->__toString();
            }


            if (!$this->getRatesPlansRooms()->offsetExists($avail_message['rate_plan_code'])) {
                throw new Exception('Relation RatePlan="' . $avail_message['rate_plan_code'] . '"" and InvTypeCode="' . $avail_message['rate_plan_code'] . '"  not found', $this->ROOM_OR_RATE_NOT_FOUND);
            }

            $RatePlanRooms = $this->getRatesPlansRooms()->offsetGet($avail_message['rate_plan_code']);
            if (!$RatePlanRooms->contains('room.channel_room.code', $avail_message['room_code'])) {
                throw new Exception('Relation RatePlan="' . $avail_message['rate_plan_code'] . '"" and InvTypeCode="' . $avail_message['room_code'] . '"  not found', $this->ROOM_OR_RATE_NOT_FOUND);
            }

            /** @var RatesPlansRooms $RatePlanRoom */
            $RatePlanRoom = $RatePlanRooms->firstWhere('room.channel_room.code', $avail_message['room_code']);

            $this->setDateRange($avail_message['start_date'], $avail_message['end_date']);
            foreach ($this->getDateRange() as $date) {
                /** @var DateTime $date */
                $dateInput = $date->format("Y-m-d");

                $calendaryDate = $RatePlanRoom->getCalendaryDate($dateInput);
                if (empty($calendaryDate)) { // create
                    $calendaryDate = $RatePlanRoom->addCalendaryDate($dateInput);
                }

                foreach ($rates as $rateType => $ratesType) {
                    if ($rateType == 'dates_description') {
                        /** @var Collection $rate_plan_room_tranlations */
                        $rate_plan_room_tranlations = $RatePlanRoom->translations->keyBy('slug');
                        if (!$rate_plan_room_tranlations->offsetExists('rate_plan_room_description')) {
                            $translation = new Translation();
                            $translation->type = 'rate_plan_room';
                            $translation->language_id = $this->getEnLang()->id;
                            $translation->object_id = $RatePlanRoom->id;
                            $translation->slug = 'rate_plan_room_description';
                            $translation->value = $ratesType;

                            $RatePlanRoom->translations->add($translation);
                        } else {
                            $rate_plan_room_tranlations->offsetGet('rate_plan_room_description')->value = $ratesType;
                        }
                    } else {
                        foreach ($ratesType as $rate) {
                            $rateSelected = $calendaryDate->getRateByType($rateType, $rate);
                            if (empty($rateSelected)) {// create
                                $calendaryDate->addRate($rate);
                            } else {
                                $rateSelected->fill($rate);
                            }
                        }
                    }
                }
            }
        }

        $this->getRatesPlansRooms()->each(function (Collection $Collection) {
            $Collection->each(function (RatesPlansRooms $RatesPlansRoom) {
                $RatesPlansRoom->translations->each(function (Translation $t) {
                    $t->save();
                });

                $RatesPlansRoom->calendarys()->saveMany($RatesPlansRoom->calendarys);
                $RatesPlansRoom->calendarys->each(function (RatesPlansCalendarys $calendary) {
                    $calendary->rate()->saveMany($calendary->rate);
                });
            });
        });
    }

    private function getEnLang()
    {
        if (!$this->_lang) {
            $this->_lang = Language::where('iso', 'en')->first();
        }

        return $this->_lang;
    }
}
