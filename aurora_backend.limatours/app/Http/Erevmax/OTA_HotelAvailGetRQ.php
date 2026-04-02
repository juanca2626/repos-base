<?php


namespace App\Http\Erevmax;

use App\Channel;
use App\Http\Erevmax\Traits\Erevmax as ErevmaxTrait;
use App\RatesPlansRooms;
use Exception;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class OTA_HotelAvailGetRQ
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
        return $this->data()->HotelRef;
    }

    /**
     * @return SimpleXMLElement
     */
    public function data()
    {
        return $this->request->OTA_HotelAvailGetRQ->HotelAvailRequests->HotelAvailRequest;
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
            'send_booking_limit' => false,
            'send_restrictions' => false,
            'start_date' => collect(),
            'end_date' => collect(),
            'rates_plans_codes' => collect(),
            'rooms_codes' => collect(),
        ]);

        if ($this->data()->attributes()->SendBookingLimit) {
            $this->filters['send_booking_limit'] = (bool)$this->data()->attributes()->SendBookingLimit->__toString();
        }

        if (isset($this->data()->RestrictionStatusCandidates) and
            isset($this->data()->RestrictionStatusCandidates->attributes()->SendAllRestrictions)) {
            $this->filters['send_restrictions'] = (bool)$this->data()->RestrictionStatusCandidates->attributes()->SendAllRestrictions->__toString();
        }

        $DateRange = $this->data()->DateRange->attributes();
        if (empty($DateRange->Start) or empty($DateRange->End)) {
            throw new Exception('Start and End date can not null or empty', $this->REQUIRED_FIELD_MISSING);
        }

        $this->filters['start_date'] = $DateRange->Start->__toString();
        $this->filters['end_date'] = $DateRange->End->__toString();

        if (isset($this->data()->RatePlanCandidates) and isset($this->data()->RatePlanCandidates->RatePlanCandidate)) {
            foreach ($this->data()->RatePlanCandidates->RatePlanCandidate as $RatePlanCandidate) {
                if (empty($RatePlanCandidate->attributes()->RatePlanCode)) {
                    throw new Exception('RatePlanCandidates.RatePlanCandidate.RatePlanCode can not null or empty', $this->REQUIRED_FIELD_MISSING);
                }
                $this->filters['rates_plans_codes']->add($RatePlanCandidate->attributes()->RatePlanCode->__toString());
            }
        }

        if (isset($this->data()->RoomTypeCandidates->RoomTypeCandidate)) {
            foreach ($this->data()->RoomTypeCandidates->RoomTypeCandidate as $RoomTypeCandidate) {
                if (empty($RoomTypeCandidate->attributes()->RoomTypeCode)) {
                    throw new Exception('RoomTypeCandidates.RoomTypeCandidate.RoomTypeCode can not null or empty', $this->REQUIRED_FIELD_MISSING);
                }
                $this->filters['rooms_codes']->add($RoomTypeCandidate->attributes()->RoomTypeCode->__toString());
            }
        }

        $this->getFilters()->transform(function ($item, $key) {
            return ($key == 'rates_plans_codes' or $key == 'rooms_codes') ? $item->unique() : $item;
        });

        $this->setDateRange($this->getFilter('start_date'), $this->getFilter('end_date'));

    }

    /**
     * @param SimpleXMLElement $simpleXMLElement
     * @return
     * @throws Exception
     */
    public function process()
    {
        $this->response = new SimpleXMLElement('<AvailStatusMessages/>');
        $this->response->addAttribute('HotelCode', $this->getHotelCode());
        /**
         * $Unique_ID : The unique identifier element allows the trading
         * partners to uniquely identify each AvailStatusMessage
         * for tracing of transactions.
         */
        $Unique_ID = 1;
        $this->getRatesPlansRooms()->each(function (Collection $RatePlanRooms) use ($Unique_ID) {
            $RatePlanRooms->each(function (RatesPlansRooms $RatePlanRoom) use ($Unique_ID) {
                $RatePlanCode = $RatePlanRoom->rate_plan->code;
                $InvTypeCode = $RatePlanRoom->room->channel_room->code;

                $calendary = $RatePlanRoom->calendarys->keyBy('date');
                $inventories = $RatePlanRoom->inventories->keyBy('date');

                $dateRangesRows = $this->getDateRangesRows($RatePlanRoom->id, $calendary, $inventories);
                foreach ($dateRangesRows as $dateRangesRow) {
                    $AvailStatusMessage = $this->response->addChild('AvailStatusMessage');

                    $UniqueID = $AvailStatusMessage->addChild('UniqueID');
                    $UniqueID->addAttribute('ID', $Unique_ID);
                    $UniqueID->addAttribute('Type', '16');
                    $Unique_ID++;

                    if ($this->getFilter('send_booking_limit')) {
                        $AvailStatusMessage->addAttribute('BookingLimit', $dateRangesRow['Inventory']['inventory_num'] ? (int)$dateRangesRow['Inventory']['inventory_num'] : 0);
                        $AvailStatusMessage->addAttribute('BookingLimitMessageType', 'SetLimit');
                    }

                    $StatusApplicationControl= $AvailStatusMessage->addChild('StatusApplicationControl');
                    $StatusApplicationControl->addAttribute('Start', $dateRangesRow['Start']);
                    $StatusApplicationControl->addAttribute('End', $dateRangesRow['End']);
                    $StatusApplicationControl->addAttribute('RatePlanCode', $RatePlanCode);
                    $StatusApplicationControl->addAttribute('InvTypeCode', $InvTypeCode);

                    if ($this->getFilter('send_restrictions')) {
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

                    if ($this->getFilter('send_restrictions')) {
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
            });
        });
    }

    private function addDateRangeRow($ratesPlansRoomId, $start, $end, Collection $dateRates, Collection $inventory = null)
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
    private function getDateRangesRows($ratesPlansRoomId, Collection $calendary, Collection $inventories)
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
            $dateRangesRows->add($this->addDateRangeRow(
                $ratesPlansRoomId,
                $this->getFilter('start_date'),
                $this->getFilter('end_date'),
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
                    $dateRangesRows->offsetSet($dateRangeIndex, $this->addDateRangeRow(
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
                        $dateRangesRows->offsetSet($dateRangeIndex, $this->addDateRangeRow(
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
}
