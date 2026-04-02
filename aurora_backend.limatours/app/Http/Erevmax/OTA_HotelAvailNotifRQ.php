<?php


namespace App\Http\Erevmax;


use App\Channel;
use App\RatesPlansRooms;

use App\Http\Erevmax\Traits\Erevmax as ErevmaxTrait;
use Exception;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class OTA_HotelAvailNotifRQ
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
        $this->ratesPlansRooms($this->hotel(), $withCalendarys = true, $withInventory = true);
    }

    /**
     * @return SimpleXMLElement
     */
    public function getHotelRef()
    {
        return $this->data()->AvailStatusMessages;
    }

    /**
     * @return SimpleXMLElement
     */
    public function data()
    {
        return $this->request->OTA_HotelAvailNotifRQ;
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
        $this->filters = collect(['start_date' => collect(), 'end_date' => collect(), 'rates_plans_codes' => collect(), 'rooms_codes' => collect()]);

        foreach ($this->data()->AvailStatusMessages->AvailStatusMessage as $AvailStatusMessage) {
            if (!isset($AvailStatusMessage->StatusApplicationControl)) {
                throw new Exception('Can not be null AvailStatusMessages.AvailStatusMessage.StatusApplicationControl', $this->REQUIRED_FIELD_MISSING);
            }

            $messageAttr = $AvailStatusMessage->StatusApplicationControl->attributes();
            if (empty($messageAttr->Start) or empty($messageAttr->End)) {
                throw new Exception('Start and End date can not null or empty', $this->REQUIRED_FIELD_MISSING);
            }

            $this->filters['start_date']->add($messageAttr->Start->__toString());
            $this->filters['end_date']->add($messageAttr->End->__toString());

            if (!empty($messageAttr->RatePlanCode)) {
                $this->filters['rates_plans_codes']->add($messageAttr->RatePlanCode->__toString());
            }

            if (!empty($messageAttr->InvTypeCode)) {
                $this->filters['rooms_codes']->add($messageAttr->InvTypeCode->__toString());
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
        foreach ($this->data()->AvailStatusMessages->AvailStatusMessage as $AvailStatusMessage) {
            $messageAttr = $AvailStatusMessage->StatusApplicationControl->attributes();

            $avail_message = collect([
                'start_date' => $messageAttr->Start->__toString(),
                'end_date' => $messageAttr->End->__toString(),
                'rate_plan_code' => '',
                'room_code' => '',
                'days_apply' => collect(),
                'inventories' => collect(),
                'restrictions' => collect(),
            ]);

            if (!empty($messageAttr->RatePlanCode)) {
                $avail_message->offsetSet('rate_plan_code', $messageAttr->RatePlanCode->__toString());
            }

            if (!empty($messageAttr->InvTypeCode)) {
                $avail_message->offsetSet('room_code', $messageAttr->InvTypeCode->__toString());
            }

            if (!empty($messageAttr->Mon) and (bool)$messageAttr->Mon->__toString()) {
                $avail_message['days_apply']->add("Mon");
            }
            if (!empty($messageAttr->Tue) and (bool)$messageAttr->Tue->__toString()) {
                $avail_message['days_apply']->add("Tue");
            }
            if (!empty($messageAttr->Weds) and (bool)$messageAttr->Weds->__toString()) {
                $avail_message['days_apply']->add("Wed");
            }
            if (!empty($messageAttr->Thur) and (bool)$messageAttr->Thur->__toString()) {
                $avail_message['days_apply']->add("Thu");
            }
            if (!empty($messageAttr->Fri) and (bool)$messageAttr->Fri->__toString()) {
                $avail_message['days_apply']->add("Fri");
            }
            if (!empty($messageAttr->Sat) and (bool)$messageAttr->Sat->__toString()) {
                $avail_message['days_apply']->add("Sat");
            }
            if (!empty($messageAttr->Sun) and (bool)$messageAttr->Sun->__toString()) {
                $avail_message['days_apply']->add("Sun");
            }

            if ($avail_message['days_apply']->isEmpty()) {
                $avail_message['days_apply'] = collect(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']);
            }

            if (isset($AvailStatusMessage->attributes()->BookingLimit)) {
                $avail_message['inventories']->offsetSet('inventory_num', (int)$AvailStatusMessage->attributes()->BookingLimit);
            }

            if (isset($AvailStatusMessage->LengthsOfStay)) {
                if (!isset($AvailStatusMessage->LengthsOfStay->LengthOfStay)) {
                    throw new Exception('LengthsOfStay.LengthOfStay', $this->REQUIRED_FIELD_MISSING);
                }

                foreach ($AvailStatusMessage->LengthsOfStay->LengthOfStay as $LengthOfStay) {
                    $MinMaxMessageType = $LengthOfStay->attributes()->MinMaxMessageType->__toString();

                    if ($MinMaxMessageType == "SetMinLOS") {
                        $avail_message['restrictions']->offsetSet('min_length_stay', empty($LengthOfStay->attributes()->Time) ? 0 : (int)$LengthOfStay->attributes()->Time);
                    } elseif ($MinMaxMessageType == "SetMaxLOS") {
                        $avail_message['restrictions']->offsetSet('max_length_stay', empty($LengthOfStay->attributes()->Time) ? 99 : (int)$LengthOfStay->attributes()->Time);
                    }
                }
            }

            if (isset($AvailStatusMessage->RestrictionStatus)) {
                foreach ($AvailStatusMessage->RestrictionStatus as $RestrictionStatus) {
                    $restrictAttr = $RestrictionStatus->attributes();
                    if (isset($restrictAttr->Restriction) and strtolower($restrictAttr->Restriction->__toString()) == "master") {
                        if (!isset($AvailStatusMessage->RestrictionStatus->attributes()->Status)) {
                            throw new Exception('RestrictionStatus.Status', $this->REQUIRED_FIELD_MISSING);
                        }

                        $avail_message['inventories']->offsetSet('locked', (strtolower($AvailStatusMessage->RestrictionStatus->attributes()->Status)) == 'open' ? 0 : 1);
                    } else {
                        if (isset($restrictAttr->MinAdvancedBookingOffset)) {
                            $avail_message['restrictions']->offsetSet('min_ab_offset', $this->getDateIntervalNum($restrictAttr->MinAdvancedBookingOffset->__toString()));
                        }
                        if (isset($restrictAttr->MaxAdvancedBookingOffset)) {
                            $avail_message['restrictions']->offsetSet('max_ab_offset', $this->getDateIntervalNum($restrictAttr->MaxAdvancedBookingOffset->__toString()));
                        }
                    }
                }
            }

            if (!empty($RatePlanCode) and !$this->getAvailableRatesPlansRooms()->offsetExists($RatePlanCode)) {
                throw new Exception('RatePlanCode ' . $RatePlanCode . ' not found', $this->ROOM_OR_RATE_NOT_FOUND);
            }

            $this->setDateRange($avail_message['start_date'], $avail_message['end_date']);

            $this->getRatesPlansRooms()->each(function (Collection $RatePlanRooms, $key) use ($avail_message) {
                if (empty($RatePlanCode) or $RatePlanCode == $key) {
                    if (!empty($InvTypeCode) and !$RatePlanRooms->contains('room.channel_room.code', $InvTypeCode)) {
                        throw new Exception('RatePlanCode="' . $key . '" and InvTypeCode="' . $InvTypeCode . '" combination not found', $this->ROOM_OR_RATE_NOT_FOUND);
                    }

                    $RatePlanRooms->each(function (RatesPlansRooms $RatePlanRoom, $key) use ($avail_message) {
                        if (empty($InvTypeCode) or $InvTypeCode == $RatePlanRoom->room->channel_room->code) {
                            foreach ($this->getDateRange() as $date) {
                                $dateInput = $date->format("Y-m-d");
                                if (!$avail_message['days_apply']->contains(date('D', strtotime($dateInput)))) {
                                    continue;
                                }

                                if ($avail_message['inventories']->isNotEmpty()) {
                                    $invenrotyDate = $RatePlanRoom->getInventoriDate($dateInput);
                                    if (!$invenrotyDate) {
                                        $invenrotyDate = $RatePlanRoom->addInventoriDate($dateInput);
                                    }

                                    if (!empty($avail_message['inventories']['inventory_num'])) {
                                        $invenrotyDate->inventory_num = (int)$avail_message['inventories']['inventory_num'];
                                    }
                                    if (isset($avail_message['inventories']['locked'])) {
                                        $invenrotyDate->locked = (int)$avail_message['inventories']['locked'];
                                    }
                                }

                                if ($avail_message['restrictions']->isNotEmpty()) {
                                    $calendaryDate = $RatePlanRoom->getCalendaryDate($dateInput);
                                    if (!$calendaryDate) {
                                        $calendaryDate = $RatePlanRoom->addCalendaryDate($dateInput);
                                    }

                                    if (!empty($avail_message['restrictions']['min_length_stay'])) {
                                        $calendaryDate->min_length_stay = (int)$avail_message['restrictions']['min_length_stay'];
                                    }
                                    if (!empty($avail_message['restrictions']['max_length_stay'])) {
                                        $calendaryDate->max_length_stay = (int)$avail_message['restrictions']['max_length_stay'];
                                    }
                                    if (!empty($avail_message['restrictions']['min_ab_offset'])) {
                                        $calendaryDate->min_ab_offset = (int)$avail_message['restrictions']['min_ab_offset'];
                                    }
                                    if (!empty($avail_message['restrictions']['max_ab_offset'])) {
                                        $calendaryDate->max_ab_offset = (int)$avail_message['restrictions']['max_ab_offset'];
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }

        $this->getAvailableRatesPlansRooms()->each(function (Collection $RatePlanRooms) {
            $RatePlanRooms->each(function (RatesPlansRooms $RatePlanRoom) {
                $RatePlanRoom->calendarys()->saveMany($RatePlanRoom->calendarys);
                $RatePlanRoom->inventories()->saveMany($RatePlanRoom->inventories);
            });
        });

        return true;
    }
}
