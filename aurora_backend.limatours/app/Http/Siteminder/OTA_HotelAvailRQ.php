<?php


namespace App\Http\Siteminder;

use App\Channel;
use App\Http\Siteminder\Traits\Siteminder as SiteminderTrait;
use App\RatesPlansCalendarys;
use App\RatesPlansRooms;
use DateTime;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class OTA_HotelAvailRQ
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

//        $this->setDataFilters();
        $this->ratesPlansRooms($this->hotel(), $withCalendarys = false, $withInventory = false);
    }

    /**
     * @return SimpleXMLElement
     */
    public function getHotelRef()
    {
        return $this->data()->HotelSearchCriteria->Criterion->HotelRef;
    }

    /**
     * @return SimpleXMLElement
     */
    public function data()
    {
        return $this->request->OTA_HotelAvailRQ->AvailRequestSegments->AvailRequestSegment;
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
        $this->response = new SimpleXMLElement('<RoomStays/>');
        if ($this->getRatesPlansRooms()->count() > 0) {
            $this->getRatesPlansRooms()->each(function (Collection $RatePlanRooms) {
                $RatePlanRooms->each(function (RatesPlansRooms $RatePlanRoom) {
                    $RoomStay = $this->response->addChild('RoomStay');
                    $RoomTypes = $RoomStay->addChild('RoomTypes')->addChild('RoomType');
                    $RoomTypes->addAttribute('RoomTypeCode', $RatePlanRoom->room->channel_room->code);

                    /** @var Collection $room_tranlations */
                    $room_tranlations = $RatePlanRoom->room->translations->keyBy('slug');

                    $RoomDescription = $RoomTypes->addChild('RoomDescription');
                    $RoomDescription->addAttribute('Name', $room_tranlations->offsetExists('room_name') ? $room_tranlations['room_name']['value'] : '');
                    $RoomDescription->addChild('Text', $room_tranlations->offsetExists('room_description') ? $room_tranlations['room_description']['value'] : '');

                    $RatePlan = $RoomStay->addChild('RatePlans')->addChild('RatePlan');
                    $RatePlan->addAttribute('RatePlanCode', $RatePlanRoom->rate_plan->code);

                    /** @var Collection $rate_plan_tranlations */
                    $rate_plan_tranlations = $RatePlanRoom->rate_plan->translations->keyBy('slug');

                    $RatePlanDescription = $RatePlan->addChild('RatePlanDescription');
                    $RatePlanDescription->addAttribute('Name', $RatePlanRoom->rate_plan->name);
                    $RatePlanDescription->addChild('Text', $rate_plan_tranlations->offsetExists('commercial_name') ? $rate_plan_tranlations['commercial_name']['value'] : '');

                    if (!$RatePlanRoom->rate_plan->status) {
                        $AdditionalDetails = $RatePlan->addChild('AdditionalDetails');
                        $AdditionalDetails->addChild('AdditionalDetail')
                            ->addAttribute('Code', 'NO_RATES');
                        $AdditionalDetails->addChild('AdditionalDetail')
                            ->addAttribute('Code', 'NO_AVAILABILITY');
                    }
                });
            });
        }
    }
}
