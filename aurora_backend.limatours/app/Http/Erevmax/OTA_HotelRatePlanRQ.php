<?php


namespace App\Http\Erevmax;


use App\Channel;
use App\Http\Erevmax\Traits\Erevmax as ErevmaxTrait;
use App\RatesPlansRooms;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class OTA_HotelRatePlanRQ
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
        $this->ratesPlansRooms($this->hotel(), $withCalendarys = true, $withInventory = false);
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
        return $this->request->OTA_HotelRatePlanRQ->RatePlans->RatePlan;
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
//            'avail_rates_only' => (bool)(strtolower($this->data()->attributes()->AvailRatesOnly) === 'true'),
            'start_date' => collect(),
            'end_date' => collect(),
            'rates_plans_codes' => collect(),
            'rooms_codes' => collect(),
        ]);

        if (isset($this->data()->DateRange)) {
            $DateRange = $this->data()->DateRange->attributes();
            if (empty($DateRange->Start) or empty($DateRange->End)) {
                throw new Exception('Start and End date can not null or empty', $this->REQUIRED_FIELD_MISSING);
            }

            if (strtotime($DateRange->Start->__toString()) > strtotime($DateRange->End->__toString())) {
                throw new Exception('Start date can not be higer than End date', $this->INVALID_PROPERTY_CODE);
            }

            $this->filters['start_date'] = $DateRange->Start->__toString();
            $this->filters['end_date'] = $DateRange->End->__toString();
        }

        if (isset($this->data()->RatePlanCandidates->RatePlanCandidate)) {
            foreach ($this->data()->RatePlanCandidates->RatePlanCandidate as $RatePlanCandidate) {
                if (empty($RatePlanCandidate->attributes()->RatePlanCode)) {
                    throw new Exception('RatePlanCandidates.RatePlanCandidate.RatePlanCode can not null or empty', $this->REQUIRED_FIELD_MISSING);
                }
                $this->requestedRoomRates['Rates'][$RatePlanCandidate->attributes()->RatePlanCode->__toString()] = $RatePlanCandidate->attributes()->RatePlanCode->__toString();
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
     * @throws Exception
     */
    public function process()
    {
        if ($this->getRatesPlansRooms()->count() > 0) {
            $this->setRoomDescriptions($this->getHotelId());
            $this->response = new SimpleXMLElement('<RatePlans/>');
            $this->response->addAttribute('HotelCode', $this->getHotelCode());
            foreach ($this->getRatesPlansRooms() as $rate_plan_code => $RatePlanRooms) {
                $_RatePlan = $RatePlanRooms->first()->rate_plan;

                $RatePlan = $this->response->addChild('RatePlan');
                $RatePlan->addAttribute('CurrencyCode', 'USD');
//                $RatePlan->addAttribute('RatePlanCode', $rate_plan_code);
                $RatePlan->addAttribute('RatePlanCode', $_RatePlan['code']);
                $RatePlan->addAttribute('RatePlanNotifType', 'New');

                if ($this->getDateRange()) {
                    $RatePlan->addAttribute('Start', $this->getFilter('start_date'));
                    $RatePlan->addAttribute('End', $this->getFilter('end_date'));
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
                $this->getFilter('start_date'),
                $this->getFilter('end_date'),
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
                    if ($currentDateRow->date != $this->getFilter('start_date')) {
                        $dateRangesRows->offsetSet($dateRangeIndex, $this->addDateRangeRow(
                            $ratesPlansRoomId,
                            $this->getFilter('start_date'),
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
            // si el rango de fechas iniciarl no inicia en  $this->getFilter('start_date') debemos
            // agregar al inicio el rango de fechas que esta por fuera
            if ($dateRangesRows->first()['Start'] != $this->getFilter('start_date')) {
                $dateRangesRows->prepend($this->addDateRangeRow(
                    $ratesPlansRoomId,
                    $this->getFilter('start_date'),
                    Carbon::parse($dateRangesRows->first()['Start'])->subDay()->format('Y-m-d'),
                    collect()
                ));
            }

            // si el rango de fechas final no termina en  $this->getFilter('end_date') debemos
            // agregar el rango de fechas que esta por fuera
            if ($dateRangesRows->last()['End'] != $this->getFilter('end_date')) {
                $dateRangesRows->add($this->addDateRangeRow(
                    $ratesPlansRoomId,
                    Carbon::parse($dateRangesRows->last()['End'])->addDay()->format('Y-m-d'),
                    $this->getFilter('end_date'),
                    collect()
                ));
            }
        }

        return $dateRangesRows;
    }
}
