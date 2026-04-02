<?php

namespace App\Http\Erevmax\Traits;

use App\Hotel;
use App\Language;
use App\RatesPlans;
use App\RatesPlansRooms;
use App\Room;
use App\Http\Traits\Channel as ChannelTrait;
use App\Http\Traits\ChannelLogs as ChannelLogsTrait;
use App\Http\Traits\Translations as TranslationsTrait;
use DateInterval;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

trait Erevmax
{
    use ChannelTrait, ChannelLogsTrait, TranslationsTrait;

    public $LIMATOURS_ID = 'LIMATOURS';    // if supply by the conection
    public $CONECTOR_CODE = 'EREVMAX';    // System currently unavailable

    public $SOAPENV = "http://schemas.xmlsoap.org/soap/envelope/";
    public $WSU = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd";
    public $WSSE = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
    public $WSA = "http://www.w3.org/2005/08/addressing";
    public $HTNG = "http://htng.org/PWSWG/2007/02/AsyncHeaders";
    public $PASSWORD_TYPE_ENCODE = "'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText'";
    public $OTA = "http://www.opentravel.org/OTA/2003/05";


    public $RequestorType = "22";
    public $RequestorID = "RateTiger";
    public $BookingChannelType = "4";
    public $UniqueIDType = "14";
    public $CompanyName = "Limatours";
    public $CreatorID = "Limatours";
    public $CompanyCode = "246800";
    public $Currency = "USD";
    public $ResUserName = "eruser";
    public $ResPassword = "n2JTUj*b9XwGn*X";
    public $ResUri = "https://postprod1.ratetiger.com:4443/ReservationListener/HTNGGenericListenerService";


    public $AUTHENTICATION_ERROR_CODE = 4;    // System currently unavailable

    public $SYSTEM_CORRENTLY_UNAVAILABLE = 187;    // System currently unavailable
    public $INVALID_PROPERTY_CODE = 400;    // Invalid property code Hotel code
    public $SYSTEM_ERROR = 448;    // System error
    public $UNABLE_TO_PROCESS = 450;    // Unable to process

    public $INVALID_RATE_CODE = 249;    // Invalid rate code
    public $REQUIRED_FIELD_MISSING = 321;    // Required field missing
    public $HOTEL_NOT_ACTIVE = 375;    // Hotel not active
    public $INVALID_HOTEL_CODE = 392;    // Invalid hotel code
    public $INVALID_ROOM_TYPE = 402;    // Invalid room type
    public $RATE_DOES_NOT_EXIST = 436;    // Rate does not exist
    public $ROOM_OR_RATE_NOT_FOUND = 783;    // Room or rate not found
    public $RATE_NOT_LOADED = 842;    // Rate not loaded


    /** @var Collection */
    public $availableRatsPlansRooms = null;
    /** @var Collection */
    public $availableRatesPlans = null;

    /**
     * @return SimpleXMLElement
     */
    private function setXMLResponse()
    {
        $xml = new SimpleXMLElement('<' . $this->responseMethodName() . '/>');
        $xml->addAttribute('xmlns', $this->OTA);
        $xml->addAttribute('Version', $this->getVersion());
        $xml->addAttribute('TimeStamp', date('c'));
        $xml->addAttribute('EchoToken', $this->getEchoToken());
        $xml->addAttribute('PrimaryLangID', $this->getPrimaryLangID());
        $xml->addAttribute('Target', $this->getTarget());

        $this->XMLResponse = $xml;

        return $this->XMLResponse;
    }

    /**
     * @param $resposne
     * @param bool $success
     * @return array
     */
    private function responseRequest($resposne, $success = false)
    {
        $xml = new SimpleXMLElement('<SOAP-ENV:Envelope/>', LIBXML_NOERROR);
        $xml->addAttribute('xmlns:xmlns:SOAP-ENV', $this->SOAPENV);

        /* BODY */
        $Body = $xml->addChild('xmlns:SOAP-ENV:Body');

        if ($success) {
            $this->xmlAppend($Body, $resposne);
        } else {
            $Fault = $Body->addChild('xmlns:SOAP-ENV:Fault');
            $Fault->addChild('faultcode', '300');
            $Fault->addChild('faultstring', $resposne);
        }

        return $xml->asXML();
    }

    /**
     * @param null $RequestedRoomRates
     */
    private function setAvailableRoomRatesOld($RequestedRoomRates = null)
    {
        $select = DB::table('channels')
            ->join('rates_plans_rooms', 'channels.id', '=', 'rates_plans_rooms.channel_id')
            ->join('rates_plans', 'rates_plans.id', '=', 'rates_plans_rooms.rates_plans_id')
            ->join('rooms', 'rooms.id', '=', 'rates_plans_rooms.room_id')
            ->join('channel_room', function ($join) {
                $join->on('rooms.id', '=', 'channel_room.room_id')
                    ->on('channels.id', '=', 'channel_room.channel_id');
            })
            ->join('hotels', 'hotels.id', '=', 'rooms.hotel_id')
            ->select([
                'channels.code as channel_code',
                'rooms.id as room_id',
                'channel_room.code as room_code',
                'rooms.state as room_status',
                'rooms.max_capacity',
                'rooms.min_adults',
                'rooms.max_adults',
                'rates_plans.id as rate_plan_id',
                'rates_plans.code as rate_plan_code',
                'rates_plans.name as rate_plan_name',
                'rates_plans_rooms.id as rates_plans_room_id',
                'rates_plans_rooms.status as rate_plan_status',
            ])
            ->where('channels.code', '=', self::CONECTOR_CODE)
            ->where('rates_plans_rooms.status', '=', '1')
            ->where('channels.status', '=', '1')
            ->where('hotels.id', '=', $this->getHotelId());

        if ($RequestedRoomRates) {
            if (!empty($RequestedRoomRates['Rooms'])) {
                $select->whereIn('channel_room.code', array_unique($RequestedRoomRates['Rooms']));
            }
            if (!empty($RequestedRoomRates['Rates'])) {
                $select->whereIn('rates_plans.code', array_unique($RequestedRoomRates['Rates']));
            }
        }

        $RoomRates = $select->get();

        $this->availableRoomRates = $RoomRates->groupBy(['rate_plan_code', 'room_code']);
        $this->RatesPlanRoomsIds = array_keys($RoomRates->groupBy(['rates_plans_room_id'])->toArray());
    }

    /**
     * @param null|Collection $RequestedRoomRates
     */
    private function setAvailableRoomRates($RequestedRoomRates = null, $getCalendarys = false)
    {
        $select = $this->hotel()->rates_plans()
            ->whereHas('rate_plans_rooms', function ($query) {
                $query->where('channel_id', '=', $this->channelId());
            })
            ->with([
                'rooms' => function ($query) use ($RequestedRoomRates) {
                    $query->whereHas('channels', function ($query) {
                        $query->where('channels.code', '=', self::CONECTOR_CODE);
                        $query->whereNotNull('channel_room.code');
                    });

                    if (!empty($RequestedRoomRates['Rooms'])) {
                        $query->whereHas('channel_room', function ($query) use ($RequestedRoomRates) {
                            $query->whereIn('code', array_unique($RequestedRoomRates['Rooms']));
                        });
                    }

                    $query->wherePivot('channel_id', $this->channelId());
                    $query->with([
                        'translations' => function ($query) {
                            $query->whereHas('language', function ($query) {
                                $query->where('iso', '=', 'en');
                            });
                        },
                        'channel_room' => function ($query) {
                            $query->where('channel_id', '=', $this->channelId());
                            $query->with([
                                'channel' => function ($query) {
                                    $query->where('id', '=', $this->channelId());
                                },
                            ]);
                        },
                    ]);
                },
                'translations' => function ($query) {
                    $query->whereHas('language', function ($query) {
                        $query->where('iso', '=', 'en');
                    });
                },
            ]);


        if (!empty($RequestedRoomRates['Rates'])) {
            $select->whereIn('code', array_unique($RequestedRoomRates['Rates']));
        }

        $RoomRates = $select->get();
        $this->availableRatesPlans = $RoomRates;

        $this->RatesPlanRoomsIds = [];
        foreach ($RoomRates as $RoomRate) {
            foreach ($RoomRate['rooms'] as $room) {
                $this->RatesPlanRoomsIds[] = $room['pivot']['id'];
            }
        }
    }

    /**
     * @param bool $inventory
     */
    private function setAvailableRatesPlansRooms($inventory = false)
    {
        $select = $this->hotel()->rates_plans_rooms()
            ->where('channel_id', '=', $this->channelId())
            ->whereHas('room', function ($query) {
                $query->whereHas('channel_room', function ($query) {
                    $query->whereNotNull('code');
                    $query->where('channel_id', '=', $this->channelId());
                    if (!empty($this->getRequestedRoomRates()['Rooms'])) {
                        $query->whereIn('code', array_unique($this->getRequestedRoomRates()['Rooms']));
                    }
                });
            })
            ->whereHas('rate_plan', function ($query) {
                $query->whereNotNull('code');
                if (!empty($this->getRequestedRoomRates()['Rates'])) {
                    $query->whereIn('code', array_unique($this->getRequestedRoomRates()['Rates']));
                }
            })
            ->with([
                'room' => function ($query) {
                    $query->with([
                        'translations' => function ($query) {
                            $query->whereHas('language', function ($query) {
                                $query->where('iso', '=', 'en');
                            });
                        },
                        'channel_room' => function ($query) {
                            $query->where('channel_id', '=', $this->channelId());
                        },
                    ]);
                },
                'rate_plan' => function ($query) {
                    $query->with([
                        'translations' => function ($query) {
                            $query->whereHas('language', function ($query) {
                                $query->where('iso', '=', 'en');
                            });
                        },
                    ]);
                },
            ])->when($this->getDateRange(), function ($query) use ($inventory) {
                return $query->with([
                    'calendarys' => function ($query) {
                        $query->whereBetween('date', [$this->getStartDate(), $this->getEndDate()]);
                        $query->with([
                            'rate'
                        ]);
                        $query->orderBy('date');
                    },
                ])->when($inventory, function ($query) {
                    return $query->with([
                        'inventories' => function ($query) {
                            $query->whereBetween('date', [$this->getStartDate(), $this->getEndDate()]);
                            $query->orderBy('date');
                        },
                    ]);
                });
            });

        $this->availableRatsPlansRooms = $select->get()
            ->groupBy(['rate_plan.code']);
    }

    /**
     * @return Collection
     */
    private function getAvailableRatesPlansRooms()
    {
        return $this->availableRatsPlansRooms;
    }

    /**
     * @param $RatePlanCode
     * @param $RooomCode
     * @return mixed|RatesPlansRooms
     * @throws Exception
     */
    private function getRatePlanRoom($RatePlanCode, $RooomCode)
    {
        if (empty($this->getAvailableRatesPlansRooms()[$RatePlanCode])) {
            throw new Exception(
                'Relation RatePlan="' . $RatePlanCode . '"" and InvTypeCode="' . $RooomCode . '"  not found',
                self::ROOM_OR_RATE_NOT_FOUND
            );
        }

        $RatesPlansRoom = $this->getAvailableRatesPlansRooms()[$RatePlanCode]->first(function ($RatesPlansRoom) use ($RooomCode) {
            return $RatesPlansRoom->room->channel_room->code == $RooomCode;
        });

        if (!$RatesPlansRoom) {
            throw new Exception(
                'Relation RatePlan="' . $RatePlanCode . '"" and InvTypeCode="' . $RooomCode . '"  not found',
                self::ROOM_OR_RATE_NOT_FOUND
            );
        }

        return $RatesPlansRoom;
    }

    /**
     * @return Collection
     */
    private function getAvailableRoomRates()
    {
        return $this->availableRoomRates;
    }

    /**
     * @return Collection
     */
    private function getAvailableRatesPlans()
    {
        return $this->availableRatesPlans;
    }

    /**
     * @param array $groupBy
     */
    private function setAvailableRoomRatesDatesOld($groupBy = ['rates_plans_room_id', 'date'])
    {
        $select = DB::table('rates_plans_rooms')
            ->join('rates_plans_calendarys', 'rates_plans_rooms.id', '=', 'rates_plans_calendarys.rates_plans_room_id')
            ->leftJoin('rates', 'rates_plans_calendarys.id', '=', 'rates.rates_plans_calendarys_id')
            ->select([
                'rates.id as rate_id',
                'rates_plans_calendarys.rates_plans_room_id',
                'rates_plans_calendarys.date',
                'rates_plans_calendarys.id as rates_plans_calendarys_id',
                'rates.num_adult',
                'rates.num_child',
                'rates.num_infant',
                'rates.price_adult',
                'rates.price_child',
                'rates.price_infant',
                'rates.price_extra',
                'rates.price_total',
            ])
            ->whereIn('rates_plans_calendarys.rates_plans_room_id', $this->RatesPlanRoomsIds)
            ->whereBetween('rates_plans_calendarys.date', [$this->getStartDate(), $this->getEndDate()]);


        $this->availableRoomRatesDates = $select->get()->groupBy($groupBy);
    }

    /**
     * @param array $groupBy
     */
    private function setAvailableRoomRatesDates($groupBy = ['rates_plans_room_id', 'date'])
    {
        $select = DB::table('rates_plans_rooms')
            ->join('rates_plans_calendarys', 'rates_plans_rooms.id', '=', 'rates_plans_calendarys.rates_plans_room_id')
            ->leftJoin('rates', 'rates_plans_calendarys.id', '=', 'rates.rates_plans_calendarys_id')
            ->select([
                'rates.id as rate_id',
                'rates_plans_calendarys.rates_plans_room_id',
                'rates_plans_calendarys.date',
                'rates_plans_calendarys.id as rates_plans_calendarys_id',
                'rates.num_adult',
                'rates.num_child',
                'rates.num_infant',
                'rates.price_adult',
                'rates.price_child',
                'rates.price_infant',
                'rates.price_extra',
                'rates.price_total',
            ])
            ->whereIn('rates_plans_calendarys.rates_plans_room_id', $this->RatesPlanRoomsIds)
            ->whereBetween('rates_plans_calendarys.date', [$this->getStartDate(), $this->getEndDate()]);


        $this->availableRoomRatesDates = $select->get()->groupBy($groupBy);

//        dd($this->RatesPlanRoomsIds);
//        dd($this->availableRoomRatesDates);
    }

    /**
     * @return Collection
     */
    private function getAvailableRoomRatesDates()
    {
        return $this->availableRoomRatesDates;
    }

    /**
     *
     */
    private function setAvailableRoomRatesInventories()
    {
        $query = DB::table('inventories')
            ->whereIn('inventories.rate_plan_rooms_id', $this->RatesPlanRoomsIds)
            ->whereBetween('inventories.date', [$this->getStartDate(), $this->getEndDate()])
            ->select([
                'inventories.id',
                'inventories.rate_plan_rooms_id',
                'inventories.date',
                'inventories.inventory_num',
                'inventories.total_booking',
                'inventories.total_canceled',
                'inventories.locked',
                'inventories.day',
            ]);

//        dd(Str::replaceArray('?', $query->getBindings(), $query->toSql()));
        $this->availableRoomRatesInventores = $query->get()
            ->groupBy(['rate_plan_rooms_id', 'date']);
    }

    /**
     * @return Collection
     */
    private function getAvailableRoomRatesInventories()
    {
        return $this->availableRoomRatesInventores;
    }

    /**
     * @param array $groupBy
     */
    private function setAvailableRoomRatesCalendary($groupBy = ['rate_plan_rooms_id', 'date'])
    {
        $query = DB::table('rates_plans_calendarys')
            ->whereNull('deleted_at')
            ->whereIn('rates_plans_calendarys.rates_plans_room_id', $this->RatesPlanRoomsIds)
            ->whereBetween('rates_plans_calendarys.date', [$this->getStartDate(), $this->getEndDate()])
            ->select([
                'rates_plans_calendarys.id',
                'rates_plans_calendarys.date',
                'rates_plans_calendarys.rates_plans_room_id as rate_plan_rooms_id',
                'rates_plans_calendarys.max_ab_offset',
                'rates_plans_calendarys.min_ab_offset',
                'rates_plans_calendarys.min_length_stay',
                'rates_plans_calendarys.max_length_stay',
            ]);

//        dd(Str::replaceArray('?', $query->getBindings(), $query->toSql()));
        $this->availableRoomRatesCalendary = $query->get()
            ->groupBy($groupBy);
    }

    /**
     * @return Collection
     */
    private function getAvailableRoomRatesCalendary()
    {
        return $this->availableRoomRatesCalendary;
    }

    /**
     * @param $HotelId
     */
    private function setRoomDescriptions($HotelId)
    {
        $this->availableRoomDescriptions = DB::table('rooms')
            ->join('translations', 'rooms.id', '=', 'translations.object_id')
            ->join('languages', 'languages.id', '=', 'translations.language_id')
            ->select([
                'translations.id',
                'translations.value',
                'translations.slug',
                'translations.object_id as room_id'
            ])
            ->where('rooms.hotel_id', '=', $HotelId)
            ->where('translations.type', '=', 'room')
            ->where('languages.iso', '=', 'en')
            ->get()
            ->groupBy(['room_id', 'slug']);
    }

    /**
     * @param SimpleXMLElement $RatePlan
     * @throws Exception
     */
    private function setRequestedRoomRatesOld(SimpleXMLElement $RatePlan)
    {
        if (!isset($RatePlan->RatePlanCandidates->RatePlanCandidate)) {
            throw new Exception(
                'RatePlanCandidates.RatePlanCandidate can not null or empty',
                self::REQUIRED_FIELD_MISSING
            );
        }
        if (!isset($RatePlan->RoomTypeCandidates->RoomTypeCandidate)) {
            throw new Exception(
                'RoomTypeCandidates.RoomTypeCandidate can not null or empty',
                self::REQUIRED_FIELD_MISSING
            );
        }

        foreach ($RatePlan->RatePlanCandidates->RatePlanCandidate as $RatePlanCandidate) {
            if (empty($RatePlanCandidate->attributes()->RatePlanCode)) {
                throw new Exception(
                    'RatePlanCandidates.RatePlanCandidate.RatePlanCode can not null or empty',
                    self::REQUIRED_FIELD_MISSING
                );
            }
            $this->requestedRoomRates['Rates'][] = $RatePlanCandidate->attributes()->RatePlanCode->__toString();
        }

        foreach ($RatePlan->RoomTypeCandidates->RoomTypeCandidate as $RoomTypeCandidate) {
            if (empty($RoomTypeCandidate->attributes()->RoomTypeCode)) {
                throw new Exception(
                    'RoomTypeCandidates.RoomTypeCandidate.RoomTypeCode can not null or empty',
                    self::REQUIRED_FIELD_MISSING
                );
            }
            $this->requestedRoomRates['Rooms'][] = $RoomTypeCandidate->attributes()->RoomTypeCode->__toString();
        }
    }

    /**
     * @param SimpleXMLElement $RatePlan
     * @throws Exception
     */
    private function setRequestedRoomRates(SimpleXMLElement $RatePlan)
    {
        if (isset($RatePlan->RatePlanCandidates->RatePlanCandidate)) {
            foreach ($RatePlan->RatePlanCandidates->RatePlanCandidate as $RatePlanCandidate) {
                if (empty($RatePlanCandidate->attributes()->RatePlanCode)) {
                    throw new Exception(
                        'RatePlanCandidates.RatePlanCandidate.RatePlanCode can not null or empty',
                        self::REQUIRED_FIELD_MISSING
                    );
                }
                $this->requestedRoomRates['Rates'][$RatePlanCandidate->attributes()->RatePlanCode->__toString()] = $RatePlanCandidate->attributes()->RatePlanCode->__toString();
            }
        }
        if (isset($RatePlan->RoomTypeCandidates->RoomTypeCandidate)) {
            foreach ($RatePlan->RoomTypeCandidates->RoomTypeCandidate as $RoomTypeCandidate) {
                if (empty($RoomTypeCandidate->attributes()->RoomTypeCode)) {
                    throw new Exception(
                        'RoomTypeCandidates.RoomTypeCandidate.RoomTypeCode can not null or empty',
                        self::REQUIRED_FIELD_MISSING
                    );
                }
                $this->requestedRoomRates['Rooms'][$RoomTypeCandidate->attributes()->RoomTypeCode->__toString()] = $RoomTypeCandidate->attributes()->RoomTypeCode->__toString();
            }
        }
    }

    /**
     * @param SimpleXMLElement $RatePlan
     * @throws Exception
     */
    private function setRequestedRoomRates2(SimpleXMLElement $RatePlan)
    {
        if (empty($RatePlan->attributes()->RatePlanCode)) {
            throw new Exception(
                'RatePlans.RatePlan.RatePlanCode can not null or empty',
                self::REQUIRED_FIELD_MISSING
            );
        }
        $this->requestedRoomRates['Rates'][$RatePlan->attributes()->RatePlanCode->__toString()] = $RatePlan->attributes()->RatePlanCode->__toString();

        if (isset($RatePlan->Rates->Rate)) {
            foreach ($RatePlan->Rates->Rate as $Rate) {
                if (empty($Rate->attributes()->InvTypeCode)) {
                    throw new Exception(
                        'RatePlans.RatePlan.Rates.Rate.InvTypeCode can not null or empty',
                        self::REQUIRED_FIELD_MISSING
                    );
                }
                $this->requestedRoomRates['Rooms'][$Rate->attributes()->InvTypeCode->__toString()] = $Rate->attributes()->InvTypeCode->__toString();
            }
        }
    }

    /**
     * @return Collection
     */
    private function getRequestedRoomRates()
    {
        return $this->requestedRoomRates;
    }

    /**
     * @param $roomCode
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|object
     */
    private function getRoomChannel($roomCode)
    {
        return $this->hotel()->rooms()->whereHas('channels', function ($query) use ($roomCode) {
            $query->where('channel_room.code', '=', $roomCode);
            $query->where('channels.code', '=', self::CONECTOR_CODE);
        })->first();
    }

    /**
     * @param $roomCode
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|object
     */
    private function getRoom($roomCode)
    {
        return $this->hotel()->rooms()->whereHas('channels', function ($query) use ($roomCode) {
            $query->where('channel_room.code', '=', $roomCode);
            $query->where('channels.code', '=', 'AURORA');
        })->first();
    }

    /**
     * @param $ratePlanCode
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|object
     */
    public function getRatePlanChannel($ratePlanCode)
    {
        return $this->hotel()->rates_plans()->where('code', '=', $ratePlanCode)->first();
    }

    /**
     * @param $roomRate
     * @throws Exception
     */
    private function createRoomRatePlan($roomRate)
    {
        $room_code = strtoupper($roomRate['InvTypeCode']);

        // Buscamos el codigo de la habitacion del channel "rooms_channels.code"
        $room = $this->getRoomChannel($room_code);
        if (!$room) {
            $room = $this->getRoom($room_code);
            // Si no existe la asociacion con el channel buscamos el codigom de aurora
            if (!$room) {
                // Si no existe creamos la habitacion y la asociamos
                $habitacion = [
                    'hotel_id' => $this->getHotelId(),
                    'state' => 1,
                    'room_type_id' => 1,
                    'min_inventory' => 0,
                ];
                switch ($room_code) {
                    case 'SGL':
                        $habitacion['max_capacity'] = 1;
                        $habitacion['min_adults'] = 1;
                        $habitacion['max_adults'] = 1;
                        $habitacion['max_child'] = 0;
                        $habitacion['max_infants'] = 0;
                        break;
                    case 'DBL':
                        $habitacion['max_capacity'] = 3;
                        $habitacion['min_adults'] = 1;
                        $habitacion['max_adults'] = 3;
                        $habitacion['max_child'] = 1;
                        $habitacion['max_infants'] = 0;
                        break;
                    case 'DBX':
                        $habitacion['max_capacity'] = 5;
                        $habitacion['min_adults'] = 2;
                        $habitacion['max_adults'] = 5;
                        $habitacion['max_child'] = 2;
                        $habitacion['max_infants'] = 1;
                        break;
                    case 'TPL':
                        $habitacion['max_capacity'] = 5;
                        $habitacion['min_adults'] = 2;
                        $habitacion['max_adults'] = 5;
                        $habitacion['max_child'] = 2;
                        $habitacion['max_infants'] = 1;
                        break;
                    default:
                        throw new Exception("InvTypeCode {$room_code} is not valid");
                }

                $room = new Room();
                foreach ($habitacion as $field => $val) {
                    $room->{$field} = $val;
                }

                $room->save();

                $translations = [];
                $languages = Language::select('id')->get();
                foreach ($languages as $language) {
                    $translations[$language->id] = [
                        ['id' => '', 'room_name' => trim($roomRate['InvTypeName'])],
                        ['id' => '', 'room_description' => trim($roomRate['InvTypeName'])],
                    ];
                }

                $this->saveMultipleTranslation($translations, 'room', $room->id);

                $room->channels()->attach($this->channel->id, ['code' => $room_code, 'state' => 1]);
                $room->channels()->attach(1, ['code' => $room_code, 'state' => 1]);
            } else {
                $room->channels()->attach($this->channel->id, ['code' => $room_code, 'state' => 1]);
            }
        }

        // Buscamos el codigo de la tarifa "rates_plans.code"
        $rate_plan_code = strtoupper($roomRate['RatePlanCode']);
        $ratePlan = $this->getRatePlanChannel($rate_plan_code);
        if (!$ratePlan) {
            // Si no existe el plan tarifario lo creamos
            $ratePlan = new RatesPlans();
            $ratePlan->code = $rate_plan_code;
            $ratePlan->name = trim($roomRate['InvTypeName']);
            $ratePlan->status = 1;
            $ratePlan->meal_id = 1;
            $ratePlan->charge_type_id = 1;
            $ratePlan->hotel_id = $this->getHotelId();
            $ratePlan->allotment = 0;
            $ratePlan->taxes = 0;
            $ratePlan->services = 0;
            $ratePlan->timeshares = 0;
            $ratePlan->promotions = 0;
            $ratePlan->rates_plans_type_id = 1;
            $ratePlan->created_at = null;
            $ratePlan->updated_at = null;
            $ratePlan->deleted_at = null;
            $ratePlan->save();

            $translations = [];
            $languages = Language::select('id')->get();
            foreach ($languages as $language) {
                $translations[$language->id] = [
                    ['id' => '', 'commercial_name' => trim($roomRate['InvTypeName'])]
                ];
            }

            $this->saveMultipleTranslation($translations, 'rates_plan', $ratePlan->id);
        }


        // Finalmente Creamos la asociacion de la habitacion con la tarifa si no existe
        if (!$ratePlanRoom = $room->rates_plan_room()->where('rates_plans_id', '=', $ratePlan->id)->where('channel_id', '=', $this->channel->id)->first()) {
            $ratePlanRoom = new RatesPlansRooms();
            $ratePlanRoom->rates_plans_id = $ratePlan->id;
            $ratePlanRoom->room_id = $room->id;
            $ratePlanRoom->channel_id = $this->channel->id;
            $ratePlanRoom->status = 1;
            $ratePlanRoom->save();
        } else {
            // Si esta inactivo lo activamos
            if (!$ratePlanRoom->status) {
                $ratePlanRoom->status = 1;
                $ratePlanRoom->save();
            }
        }
    }

    /**
     * Registrar la habitacion
     *
     * @param $roomRate
     * @throws Exception
     */
    private function updateRoomRatePlan($roomRate)
    {
        $room_code = strtoupper($roomRate['InvTypeCode']);

        // Buscamos el codigo de la habitacion del channel "rooms_channels.code"
        if (!$room = $this->getRoomChannel($room_code)) {
            throw new Exception("InvTypeCode {$room_code} is not valid");
        }

        // Buscamos el codigo de la tarifa "rates_plans.code"
        $rate_plan_code = strtoupper($roomRate['RatePlanCode']);
        if (!$ratePlan = RatesPlans::select()->where('code', '=', $rate_plan_code)->where('hotel_id', '=', $this->getHotelId())->first()) {
            throw new Exception("RatePlanCode {$rate_plan_code} is not valid");
        }

        // Finalmente Creamos la asociacion de la habitacion con la tarifa si no existe
        if (!$ratePlanRoom = RatesPlansRooms::select()
            ->where('rates_plans_id', '=', $ratePlan->id)
            ->where('room_id', '=', $room->id)
            ->where('channel_id', '=', $this->channel->id)->first()) {
            throw new Exception("InvTypeCode / RatePlanCode is not valid");
        } else {
            // Actualizar

        }
    }

    /**
     * @param $roomRate
     * @throws Exception
     */
    private function removeRoomRatePlen($roomRate)
    {
        $room_code = strtoupper($roomRate['InvTypeCode']);

        // Buscamos el codigo de la habitacion del channel "rooms_channels.code"
        if (!$room = $this->getRoomChannel($room_code)) {
            throw new Exception("InvTypeCode {$room_code} is not valid");
        }

        // Buscamos el codigo de la tarifa "rates_plans.code"
        $rate_plan_code = strtoupper($roomRate['RatePlanCode']);
        if (!$ratePlan = RatesPlans::select()->where('code', '=', $rate_plan_code)->where('hotel_id', '=', $this->getHotelId())->first()) {
            throw new Exception("RatePlanCode {$rate_plan_code} is not valid");
        }

        // Finalmente Creamos la asociacion de la habitacion con la tarifa si no existe
        if (!$ratePlanRoom = RatesPlansRooms::select()
            ->where('rates_plans_id', '=', $ratePlan->id)
            ->where('room_id', '=', $room->id)
            ->where('channel_id', '=', $this->channel->id)->first()) {
            throw new Exception("InvTypeCode / RatePlanCode is not valid");
        } else {
            // Desactivar
            $ratePlan->status = 0;
            $ratePlan->update();
        }
    }

    /**
     * @param string $methodName
     * @return mixed
     */
    private function setClientRequest($methodName)
    {
        $this->createEchoToken();
        $this->XMLSoapREquest = new SimpleXMLElement('<SOAP-ENV:Envelope/>', LIBXML_NOERROR);
        $this->XMLSoapREquest->addAttribute('xmlns:xmlns:SOAP-ENV', $this->SOAPENV);

        /* HEADER */
        $Header = $this->XMLSoapREquest->addChild('SOAP-ENV:SOAP-ENV:Header');
        $Header->addAttribute('xmlns:xmlns:wsu', $this->WSU);
        $Header->addAttribute('xmlns:xmlns:wsa', $this->WSA);
        $Header->addAttribute('xmlns:xmlns:wsse', $this->WSSE);
        $Header->addChild('wsa:wsa:MessageID', $this->getEchoToken());
        $Header->addChild('htng:htng:CorrelationID', $this->getEchoToken())
            ->addAttribute('xmlns:xmlns:htng', $this->HTNG);
        $Header->addChild('wsa:wsa:Action', $methodName);
        $Header->addChild('wsa:wsa:ReplyTo')
            ->addChild('wsa:wsa:Address', 'http://www.w3.org/2005/08/addressing/anonymous');
        $Header->addChild('htng:htng:ReplyTo', $this->getEchoToken())
            ->addAttribute('xmlns:xmlns:htng', $this->HTNG);
        $Header->addChild('wsa:wsa:To', $this->ResUri);
        $Header->addChild('wsa:wsa:From')
            ->addChild('wsa:wsa:Address', 'urn:connect');

        /* Security */
        $Security = $Header->addChild('wsse:wsse:Security');
        $Security->addAttribute('env:env:mustUnderstand', '1');
        $Security->addAttribute('xmlns:xmlns:env', $this->SOAPENV);
        $Security->addAttribute('xmlns:xmlns:wsse', $this->WSSE);

        /* UsernameToken */
        $UsernameToken = $Security->addChild('wsse:wsse:UsernameToken');
        $UsernameToken->addAttribute('wsu:wsu:Id', 'SecurityToken-cc2aa800-ee36-4539-b00e-60c3f5c1c62b');
        $UsernameToken->addChild('wsse:wsse:Username', $this->ResUserName);
        $UsernameToken->addChild('wsse:wsse:Password', $this->ResPassword)
            ->addAttribute('Type', $this->PASSWORD_TYPE_ENCODE);
        $UsernameToken->addChild('wsse:wsse:Created', date('c'));

        /* BODY */
        $Body = $this->XMLSoapREquest->addChild('SOAP-ENV:SOAP-ENV:Body');

        $methodXML = $Body->addChild($methodName);
        $methodXML->addAttribute('EchoToken', $this->getEchoToken());
        $methodXML->addAttribute('TimeStamp', date('c'));
        $methodXML->addAttribute('Target', 'Development');
        $methodXML->addAttribute('Version', '1.003');
        $methodXML->addAttribute('xmlns', $this->OTA);

        return $methodXML;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param string $ResStatus
     * @return mixed
     */
    private function setClientRequestPos(SimpleXMLElement $xml, string $ResStatus)
    {
        $xml->addAttribute('ResStatus', $ResStatus); // Reservation status. Must be one of the following: Commit, Cancel, Modify

        $POS = $xml->addChild('POS');
        $Source = $POS->addChild('Source');
        $RequestorID = $Source->addChild('RequestorID');
        $RequestorID->addAttribute('ID', $this->RequestorID);
        $RequestorID->addAttribute('Type', $this->RequestorType);

        $BookingChannel = $Source->addChild('BookingChannel');
        $BookingChannel->addAttribute("Primary", "true");
        $BookingChannel->addAttribute("Type", $this->BookingChannelType);

        $CompanyName = $BookingChannel->addChild('CompanyName', $this->CompanyName);
        $CompanyName->addAttribute("Code", $this->CompanyCode);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param $ResID_Value
     * @return mixed
     */
    private function setClientRequestReservation(SimpleXMLElement $xml, $ResID_Value, $ResStatus)
    {
        $HotelReservations = $xml->addChild('HotelReservations');
        $HotelReservation = $HotelReservations->addChild('HotelReservation');
        $HotelReservation->addAttribute('CreateDateTime', date('c')); // This is the date when the reservation was first made.
        $HotelReservation->addAttribute('CreatorID ', $this->CreatorID); // The CreatorID is the identifier of the user or, in its absence, the office that originated the reservation
        $HotelReservation->addAttribute('ResStatus', $ResStatus);
//        $HotelReservation->addAttribute('LastModifyDateTime', ''); // Opcional
//        $HotelReservation->addAttribute('RoomStayReservation', 'true');

        $UniqueID = $HotelReservation->addChild('UniqueID');
        $UniqueID->addAttribute("ID", $ResID_Value); // identificador unico para las reservas de ReservaHotel
        $UniqueID->addAttribute("Type", $this->UniqueIDType);// Reservation ID type. Currently only "10" is supported

        return $HotelReservation;
    }

    /**
     * Create, modify and cancel of reservations.
     *
     * @param $hotelRes
     * @param bool $isCertificationTest
     * @return array
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function setClientResponse($xml)
    {
        $this->XMLSoapResponse = new SimpleXMLElement($xml);
    }

    /**
     * @return SimpleXMLElement
     */
    public function getClientResponseBody()
    {
        return $this->XMLSoapResponse->children($this->SOAPENV)->Body;
    }

    /**
     * @return SimpleXMLElement
     */
    public function setClientResponseBodyContent()
    {
        return $this->getClientResponseBody()->children($this->OTA);
    }

    /**
     * Create, modify and cancel of reservations.
     *
     * @param $hotelRes
     * @param bool $isCertificationTest
     * @return array
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelResNotifRQ(array $hotelRes, $cancel = false)
    {
        $this->setChannel('EREVMAX');
        $this->setDateRange($hotelRes['check_in'], $hotelRes['check_out']);

        $xml = $this->setClientRequest('OTA_HotelResNotifRQ');

        if ($cancel) {
            $this->setClientRequestPos($xml, 'Cancel');
            $HotelReservation = $this->setClientRequestReservation($xml, $hotelRes['channel_reservation_code'], 'Cancel');
        } else {
            $this->setClientRequestPos($xml, 'Commit');
            $HotelReservation = $this->setClientRequestReservation($xml, $hotelRes['reservation_code'], 'Commit');
        }

        /** @var SimpleXMLElement $RoomStays */
        $RoomStays = $HotelReservation->addChild('RoomStays');
        /** @var SimpleXMLElement $ResGuests */
        $ResGuests = $HotelReservation->addChild('ResGuests'); // opcional

        $setPrincipalGuest = true;
        $RPH = 1;
        $totalReservation = 0;
        foreach ($hotelRes['rooms'] as $Room) {
            $RoomsGestsType = [];
            // AgeQualifyingCode = 10; // adults
            // AgeQualifyingCode = 8; // childs
            $customer_name = explode(' ', $hotelRes['customer_name']);
            $GivenName = $customer_name[0];
            array_shift($customer_name);
            $Surname = implode(' ', $customer_name);
            $RoomsGestsType[10][0] = [
                'GivenName' => $GivenName,
                'Surname' => $Surname,
                'AgeQualifyingCode' => '10'
            ];

            for ($i = 1; $i <= (($Room['adult_num'] + $Room['extra_num']) - 1); $i++) {
                $RoomsGestsType[10][] = [
                    'GivenName' => 'adult',
                    'Surname' => 'adult',
                    'AgeQualifyingCode' => '10'
                ];
            }

            for ($i = 0; $i < $Room['child_num']; $i++) {
                $RoomsGestsType[8][] = [
                    'GivenName' => 'child',
                    'Surname' => 'child',
                    'AgeQualifyingCode' => '8'
                ];
            }

            foreach ($RoomsGestsType as $RoomGestsType) {
                foreach ($RoomGestsType as $Guests) {
                    $ResGuest = $ResGuests->addChild('ResGuest');
                    if ($setPrincipalGuest) {
                        $ResGuest->addAttribute('PrimaryIndicator', 'true');
                        $PrimaryIndicator = false;
                    } else {
                        $ResGuest->addAttribute('PrimaryIndicator', 'false');
                    }
                    $ResGuest->addAttribute('ResGuestRPH', $RPH); // Index by which this guest can be referenced within the RoomStay elements

                    $Profile = $ResGuest->addChild('Profiles')->addChild('ProfileInfo')->addChild('Profile');
                    $Profile->addAttribute('ProfileType', '1'); // The Type attribute refers to OpenTravel Alliance code type UIT and the choices recommended by HTNG should be:  1 – Customer

                    $Customer = $Profile->addChild('Customer');

                    $PersonName = $Customer->addChild('PersonName');
                    $PersonName->addChild('GivenName', $Guests['GivenName']);
                    $PersonName->addChild('MiddleName');
                    $PersonName->addChild('Surname', $Guests['Surname']);

                    $Telephone = $Customer->addChild('Telephone');
                    $Telephone->addAttribute('PhoneLocationType', '7');

                    $Address = $Customer->addChild('Address');
                    $Address->addAttribute('Type', '0');

                    if (!empty($Guests['Country'])) {
//                        $Address->addChild('CountryName', $Guests['Country'])
                        $Address->addChild('CountryName')
                            ->addAttribute('Code', $Guests['Country']);
                    }
                    break 2;
                }
            }
            /** @var SimpleXMLElement $RoomStay */
            $RoomStay = $RoomStays->addChild('RoomStay');

            // RatePlan Data
            $RatePlan = $RoomStay->addChild('RatePlans')->addChild('RatePlans');
            $RatePlan->addAttribute('RatePlanCode', $Room['rate_plan_code']);
            $RatePlan->addChild('RatePlanInclusions')->addAttribute('TaxInclusive', 'true');
            $RatePlan->addChild('MealsIncluded')->addAttribute('MealPlanIndicator', 'false');

            // RoomRate
            $RoomRate = $RoomStay->addChild('RoomRates')->addChild('RoomRate');
            $RoomRate->addAttribute('NumberOfUnits', '1'); // Number of rooms booked with these stay details. Currently only a value of '1' is supported. Additional rooms must be specified by individual RoomStay elements instead.
            $RoomRate->addAttribute('RatePlanCode', $Room['rate_plan_code']); // Rate plan identifier
            $RoomRate->addAttribute('RoomTypeCode', $Room['room_code']); // Room type identifier. This is expected to match across all RoomRate elements within the given RoomStay

            // RoomRate Dates
            $totalByRoom = 0;
            $Rates = $RoomRate->addChild('Rates');
            foreach ($Room['rates'] as $dateInfo) {
                $totalByRoom += $dateInfo['total_amount_base'];

                $Rate = $Rates->addChild('Rate');
                $Rate->addAttribute('EffectiveDate', $dateInfo['date']); // Effective (start) date of pricing date range (inclusive)
                $Rate->addAttribute('ExpireDate', (new \DateTime($dateInfo['date']))->modify('+1 day')->format('Y-m-d')); // Expire (end) date of pricing date range (exclusive)
                $Rate->addAttribute('RateTimeUnit', 'Day'); // Can be one of the following five values: Per night, Per person, Per person per night, Per stay, Per use
                $Rate->addAttribute('UnitMultiplier', '1');
                //$Rate->addAttribute('RoomPricingType', 'Per night'); // Can be one of the following five values: Per night, Per person, Per person per night, Per stay, Per use

                $Base = $Rate->addChild('Base');
                $Base->addAttribute('AmountAfterTax', priceRound($dateInfo['total_amount_base'])); // Base price after tax. Either this or @AmountBeforeTax are needed, but it is not necessary to include both.
                $Base->addAttribute('CurrencyCode', $this->Currency);
            }

            // GuestCounts
            $GuestCounts = $RoomStay->addChild('GuestCounts');//Contains the guest counts of the booking
            $GuestCounts->addAttribute('IsPerRoom ', 'true'); // Indicates that the guest count provided is on a per room basis
            foreach ($RoomsGestsType as $AgeQualifyingCode => $Guests) {
                $GuestCount = $GuestCounts->addChild('GuestCount'); // Contains the type and number of guests included in the reservation
                $GuestCount->addAttribute('AgeQualifyingCode', $AgeQualifyingCode); // The two codes used from the OpenTravel Alliance AQC code list are: 8-Child, 10-Adult
                $GuestCount->addAttribute('Count', count($Guests)); // Count will include the number and will be repeated for each Type of guest
            }

            // TimeSpan
            $TimeSpan = $RoomStay->addChild('TimeSpan');
            $TimeSpan->addAttribute('End', $hotelRes['check_out']); // Check out date of this stay
            $TimeSpan->addAttribute('Start', $hotelRes['check_in']); // Check in date of this stay

            // Room Guarantee
            $Guarantee = $RoomStay->addChild('Guarantee');
            $Guarantee->addAttribute('GuaranteeType', 'None');
            $Guarantee->addAttribute('GuaranteeCode', 'None');
            // aqui tambien colocamos el id de reserva_hotel que sera usado como numero de vaucher del lado de erevmax
            $Guarantee->addChild('GuaranteeDescription')->addChild('Text', $hotelRes['reservation_code']);

            $GuaranteesAccepted = $Guarantee->addChild('GuaranteesAccepted');
            $GuaranteeAccepted = $GuaranteesAccepted->addChild('GuaranteeAccepted');
            $PaymentCard = $GuaranteeAccepted->addChild('PaymentCard');
            $PaymentCard->addAttribute('CardNumber', '');
            $PaymentCard->addAttribute('SeriesCode', '');
            $PaymentCard->addAttribute('CardCode', '');
            $PaymentCard->addAttribute('CardType', '');
            $PaymentCard->addAttribute('ExpireDate', '');
            $PaymentCard->addChild('CardHolderName');

            // Total
            $Total = $RoomStay->addChild('Total');
            $Total->addAttribute('AmountAfterTax', priceRound($totalByRoom)); // Total price AFTER tax. Either this or @AmountBeforeTax are needed, but it is not necessary to include both.
            $Total->addAttribute('CurrencyCode', $this->Currency);

            $Total_Taxes = $Total->addChild('Taxes');
            $Total_Taxes->addAttribute('Amount', 0);
            $Total_Taxes->addAttribute('CurrencyCode', $this->Currency);

            $Total_Taxe = $Total_Taxes->addChild('Tax');
            $Total_Taxe->addAttribute('Amount', 0);
            $Total_Taxe->addAttribute('CurrencyCode', $this->Currency);

            $totalReservation += $totalByRoom;

            //BasicPropertyInfo
            $RoomStay->addChild('BasicPropertyInfo')->addAttribute('HotelCode', $hotelRes['hotel_channel_code']);

            // ResGuestRPHs
            $RoomStay->addChild('ResGuestRPHs')->addChild('ResGuestRPH')->addAttribute('RPH', $RPH);// Guest RPH associated with this RoomStay
            $RPH++;

            $Comments = $RoomStay->addChild('Comments');

            // aqui tambien colocamos el id de reserva_hotel que sera usado como numero de vaucher del lado de erevmax
            $Comment = $Comments->addChild('Comment');
            $Comment->addAttribute('GuestViewable', 1);
            $Comment->addChild('Text', 'Voucher no ' . $hotelRes['reservation_code']);

            // agregamos los comentario de los usuarios si vienen
            if ($Room['guest_note'] != '') {
                $Comments->addChild('Comment')->addChild('Text', $Room['guest_note']);
            }

            $SpecialRequest = $RoomStay->addChild('SpecialRequests')->addChild('SpecialRequest');
            $SpecialRequest->addAttribute('CodeContext', '');
            $SpecialRequest->addAttribute('RequestCode', '');
        }

        /** @var SimpleXMLElement $HotelReservationID */
        $HotelReservationID = $HotelReservation->addChild('ResGlobalInfo')
            ->addChild('HotelReservationIDs')
            ->addChild('HotelReservationID');
        $HotelReservationID->addAttribute('ResID_Source', 'RATETIGER'); // Reservation ID type. Currently only "14" is supported
        $HotelReservationID->addAttribute('ResID_Type', $this->UniqueIDType); // Reservation ID type. Currently only "14" is supported

        if ($cancel) {
            $HotelReservationID->addAttribute('ResID_Value', $hotelRes['channel_reservation_code']);// identificador unico para las reservas de ReservaHotel
        } else {
            $HotelReservationID->addAttribute('ResID_Value', $hotelRes['reservation_code']);// identificador unico para las reservas de ReservaHotel
        }

//        header("Content-Type:text/xml");
//        die($this->XMLSoapREquest->asXML());

        if (App::environment('local') === true) {
            //TODO: colocar aqui el endpoint de produccion del channel
//            $this->ResUri = 'res-uri';
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->ResUri,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array('Content-Type: text/xml'),
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $this->XMLSoapREquest->asXML(),
        ]);

        $responseString = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $status = $info['http_code'];
        $requestHeaders = $info['request_header'];
        $headerSize = $info['header_size'];
        // extract headers
        $responseHeaders = substr($responseString, 0, $headerSize);
        // extract body
        $bodyResponse = substr($responseString, $headerSize);

        $request = $this->request($this->getHeaders($requestHeaders), $this->XMLSoapREquest->asXML());
        $response = new \Illuminate\Http\Response($bodyResponse, $status, $this->getHeaders($responseHeaders));

        $error = '';
        $channel_reservation_code = '';
        try {
            if ($status !== 200) {
                throw new \Exception($bodyResponse);
            }
//            header("Content-Type:text/xml");
//            die($response->getContent());
            $this->setClientResponse($response->getContent());
            $objXml = $this->setClientResponseBodyContent();
            if (!$objXml) {
                $error = $response->getContent();
            } else {
                $errorArray = [];
                if (!empty($objXml->OTA_HotelResNotifRS->Errors)) {
                    if ($objXml->OTA_HotelResNotifRS->Errors->Error) {
                        foreach ($objXml->OTA_HotelResNotifRS->Errors->Error as $value) {
                            $errorArray[] = (string)$value;
                        }
                    }
                }

                if (!empty($objXml->OTA_HotelResNotifRS->Warnings)) {
                    if ($objXml->OTA_HotelResNotifRS->Warnings->Warning) {
                        foreach ($objXml->OTA_HotelResNotifRS->Warnings->Warning as $value) {
                            $errorArray[] = (string)$value;
                        }
                    }
                }

                if (count($errorArray) > 0) {
                    $error = implode(' | ', $errorArray);
                }
            }

            if ($objXml->OTA_HotelResNotifRS->HotelReservations->HotelReservation->ResGlobalInfo->HotelReservationIDs->HotelReservationID->attributes()->ResID_SourceContext->__toString() != 'RTConfirmNumber') {
                $error .= "ErevMAx : ResID_SourceContext not RTConfirmNumber - ResID_Value : " . $hotelRes['reservation_code'];
            }

            if ($error != '') {
                throw new \Exception($error);
            } else {
                $channel_reservation_code = trim($objXml->OTA_HotelResNotifRS->HotelReservations->HotelReservation->ResGlobalInfo->HotelReservationIDs->HotelReservationID->attributes()->ResID_Value->__toString());
                if (!$channel_reservation_code) {
                    throw new \Exception('Empty value OTA_HotelResNotifRS.HotelReservations.HotelReservation.ResGlobalInfo.HotelReservationIDs.HotelReservationID.ResID_Value');
                }
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        $success = (bool)!$error;

        if ($cancel) {
            $this->putXmlLog($request, $response, $this->getChannel(), 'OTA_HotelResCancelRQ', $hotelRes['reservation_code'], $success);
        } else {
            $this->putXmlLog($request, $response, $this->getChannel(), 'OTA_HotelResNotifRQ', $hotelRes['reservation_code'], $success);
        }

        return [
            'success' => $success ? true : false,
            'error' => $error,
            'channel_reservation_code' => $channel_reservation_code
        ];
    }

    private function getHeaders($headerText)
    {
        $headers = array();
        foreach (explode("\r\n", $headerText) as $i => $line) {
            if (trim($line) == '') {
                continue;
            }

            if (strpos($line, "HTTP/") !== false) {
                $headers['http_code'] = trim($line);
            } else {
                //some headers will contain ":" character (Location for example), and the part after ":" will be lost, Thanks to @Emanuele
                list ($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        return $headers;
    }

    public function request(array $headers = [], $content = null)
    {
        $request = new \Illuminate\Http\Request([], [], [], [], [], $_SERVER, $content);
        $request->headers->replace($headers);

        // como somos quien hace el request somo el REMOTE_ADDR de la ip
        $request->server->add(['REMOTE_ADDR', $_SERVER['SERVER_ADDR']]);

        return $request;
    }


    ///////////////////////
    /// refactorizacion ///
    ///////////////////////

    /** @var Collection */
    public $filters;

    /**
     * @return SimpleXMLElement
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return Collection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getFilterOffset($key)
    {
        if (!$this->filters){
            $this->filters = collect();
        }

        return $this->filters->offsetExists($key) ? $this->filters->offsetGet($key) : collect();
    }

    /**
     * @param $key
     * @return mixed
     */
    public function hasFilter($key)
    {
        return $this->getFilterOffset($key)->isNotEmpty();
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getFilter($key)
    {
        return $this->getFilterOffset($key);
    }

    /**
     * @param $interval
     * @return string
     * @throws Exception
     */
    public function getDateIntervalNum($interval)
    {
        if (!is_numeric($interval)) {
            $interval = (new DateInterval($interval))->format('%d');
        }
        return (int)$interval;
    }

    /**
     * @return Collection
     */
    private function getRatesPlansRooms()
    {
        return $this->availableRatsPlansRooms;
    }

    /**
     * @param Hotel $hotel
     * @param bool $withCalendarys
     * @param bool $withInventory
     */
    private function ratesPlansRooms(Hotel $hotel, $withCalendarys = false, $withInventory = false)
    {
        $select = $hotel->rates_plans_rooms()
            ->where('channel_id', '=', $this->channelId())
            ->whereHas('room', function ($query) {
                $query->whereHas('channel_room', function ($query) {
                    $query->whereNotNull('code');
                    $query->where('channel_id', '=', $this->channelId());
                    if ($this->hasFilter('rooms_codes')) {
                        $query->whereIn('code', $this->getFilter('rooms_codes'));
                    }
                });
            })
            ->whereHas('rate_plan', function ($query) {
                $query->whereNotNull('code');
                if (!empty($this->hasFilter('rates_plans_codes'))) {
                    $query->whereIn('code', $this->getFilter('rates_plans_codes'));
                }
            })
            ->with([
                'room' => function ($query) {
                    $query->with([
                        'translations' => function ($query) {
                            $query->whereHas('language', function ($query) {
                                $query->where('iso', '=', 'en');
                            });
                        },
                        'channel_room' => function ($query) {
                            $query->where('channel_id', '=', $this->channelId());
                        },
                    ]);
                },
                'rate_plan' => function ($query) {
                    $query->with([
                        'translations' => function ($query) {
                            $query->whereHas('language', function ($query) {
                                $query->where('iso', '=', 'en');
                            });
                        },
                    ]);
                },
            ])->when($this->getFilter('start_date') and $this->getFilter('end_date'), function ($query) use ($withCalendarys, $withInventory) {
                return $query->when($withCalendarys, function ($query) {
                    return $query->with([
                        'calendarys' => function ($query) {
                            $query->whereBetween('date', [$this->getFilter('start_date'), $this->getFilter('end_date')]);
                            $query->with(['rate']);
                            $query->orderBy('date');
                        },
                    ]);
                })->when($withInventory, function ($query) {
                    return $query->with([
                        'inventories' => function ($query) {
                            $query->whereBetween('date', [$this->getFilter('start_date'), $this->getFilter('end_date')]);
                            $query->orderBy('date');
                        },
                    ]);
                });
            });

        $this->availableRatsPlansRooms = $select->get()
            ->groupBy(['rate_plan.code']);
    }
}
