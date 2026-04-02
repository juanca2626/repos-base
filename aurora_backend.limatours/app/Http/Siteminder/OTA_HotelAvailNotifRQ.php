<?php


namespace App\Http\Siteminder;


use App\Channel;
use App\Http\Siteminder\Traits\Siteminder as SiteminderTrait;
use App\RatesPlansRooms;
use DateTime;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class OTA_HotelAvailNotifRQ
{
    use SiteminderTrait;

    /** @var SimpleXMLElement */
    public $request;
    /** @var SimpleXMLElement */
    public $response;

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
        return $this->request->OTA_HotelAvailNotifRQ->AvailStatusMessages;
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

        foreach ($this->data()->AvailStatusMessage as $AvailStatusMessage) {
            if (!isset($AvailStatusMessage->StatusApplicationControl)) {
                throw new Exception('Requiered node missing OTA_HotelRateAmountNotifRQ.AvailStatusMessages.AvailStatusMessage.StatusApplicationControl');
            }

            $attrs = $AvailStatusMessage->StatusApplicationControl->attributes();
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
        foreach ($this->data()->AvailStatusMessage as $AvailStatusMessage) {
            $messages = array();
            if (isset($AvailStatusMessage->attributes()->BookingLimit)) {
                $messages['inventories']['inventory_num'] = (int)$AvailStatusMessage->attributes()->BookingLimit;
            }

            $attrs = $AvailStatusMessage->StatusApplicationControl->attributes();

            $messages['start_date'] = $attrs->Start->__toString();
            $messages['end_date'] = $attrs->End->__toString();
            $messages['room_code'] = $attrs->InvTypeCode->__toString();
            $messages['rate_plan_code'] = $attrs->RatePlanCode->__toString();

            if (isset($AvailStatusMessage->RestrictionStatus)) {
                if (!isset($AvailStatusMessage->RestrictionStatus->attributes()->Status)) {
                    throw new Exception('RestrictionStatus.Status', $this->REQUIRED_FIELD_MISSING);
                }
                $messages['inventories']['locked'] = (strtolower($AvailStatusMessage->RestrictionStatus->attributes()->Status)) == 'open' ? 0 : 1;
            }

            if (isset($AvailStatusMessage->LengthsOfStay)) {
                if (!isset($AvailStatusMessage->LengthsOfStay->LengthOfStay)) {
                    throw new Exception('LengthsOfStay.LengthOfStay', $this->REQUIRED_FIELD_MISSING);
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

            if (!$this->getRatesPlansRooms()->offsetExists($messages['rate_plan_code'])) {
                throw new Exception('RatePlanCode="' . $messages['rate_plan_code'] . '"" not found', $this->ROOM_OR_RATE_NOT_FOUND);
            }

            $RatePlanRooms = $this->getRatesPlansRooms()->offsetGet($messages['rate_plan_code']);

            if (!$RatePlanRooms->contains('room.channel_room.code', $messages['room_code'])) {
                throw new Exception('Relation RatePlan="' . $messages['rate_plan_code'] . '"" and InvTypeCode="' . $messages['room_code'] . '"  not found', $this->ROOM_OR_RATE_NOT_FOUND);
            }

            /** @var RatesPlansRooms $RatePlanRoom */
            $RatePlanRoom = $RatePlanRooms->firstWhere('room.channel_room.code', $messages['room_code']);

            $this->setDateRange($messages['start_date'], $messages['end_date']);
            foreach ($this->getDateRange() as $date) {
                /** @var DateTime $date */
                $dateInput = $date->format("Y-m-d");

                if (isset($messages['restrictions'])) {
                    $calendaryDate = $RatePlanRoom->getCalendaryDate($dateInput);
                    if (empty($calendaryDate)) { // create
                        $calendaryDate = $RatePlanRoom->addCalendaryDate($dateInput);
                    }

                    if (isset($messages['restrictions']['min_length_stay'])) {
                        $calendaryDate->min_length_stay = $messages['restrictions']['min_length_stay'];
                    }

                    if (isset($messages['restrictions']['max_length_stay'])) {
                        $calendaryDate->max_length_stay = $messages['restrictions']['max_length_stay'];
                    }
                }

                if (isset($messages['inventories'])) {
                    $inventoriDate = $RatePlanRoom->getInventoriDate($dateInput);
                    if (empty($inventoriDate)) { // create
                        $inventoriDate = $RatePlanRoom->addInventoriDate($dateInput);
                    }

                    if (isset($messages['inventories']['inventory_num'])) {
                        $inventoriDate->inventory_num = $messages['inventories']['inventory_num'];
                    }

                    if (isset($messages['inventories']['locked'])) {
                        $inventoriDate->locked = $messages['inventories']['locked'];
                    }
                }
            }
        }

        $this->getRatesPlansRooms()->each(function (Collection $Collection) {
            $Collection->each(function (RatesPlansRooms $RatesPlansRoom) {
                $RatesPlansRoom->calendarys()->saveMany($RatesPlansRoom->calendarys);
                $RatesPlansRoom->inventories()->saveMany($RatesPlansRoom->inventories);
            });
        });
    }
}
