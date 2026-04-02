<?php

namespace App\Http\Siteminder\Traits;

use App\Hotel;
use App\Http\Traits\Channel as ChannelTrait;
use App\Http\Traits\ChannelLogs as ChannelLogsTrait;
use App\Http\Traits\Translations as TranslationsTrait;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
//use App\Http\Traits\Translations;
use App\Http\Traits\Channel;
use App\Http\Traits\ChannelLogs;

trait Siteminder
{
    use ChannelTrait, ChannelLogsTrait, TranslationsTrait;

    public $SOAPENV = "http://schemas.xmlsoap.org/soap/envelope/";
    public $WSSE = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
    public $PASSWORD_TYPE_ENCODE = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText";
    public $OTA = "http://www.opentravel.org/OTA/2003/05";
    public $RequestorType = "22";
    public $RequestorID = "LMT";
    public $UniqueIDType = "14";
    public $CompanyName = "Limatours";
    public $CompanyCode = "LMT";
    public $Currency = "USD";
    public $ResUserName = "LimaToursTest";
    public $ResPassword = "1bs5NmCKoXZOsP584wDEU3e2";
    public $ResUri = "https://cmtpi.siteminder.com/siteconnect/services";


    /**
     * @return SimpleXMLElement
     */
    private function setXMLResponse()
    {
        $xml = new SimpleXMLElement('<' . $this->responseMethodName() . '/>');
        $xml->addAttribute('xmlns', self::OTA);
        $xml->addAttribute('Version', $this->getVersion());
        $xml->addAttribute('TimeStamp', date('c'));
        $xml->addAttribute('EchoToken', $this->getEchoToken());

        $this->XMLResponse = $xml;

        return $this->XMLResponse;
    }

    /**
     * @param $resposne
     * @param bool $success
     * @param int $responeCode
     * @return array
     */
    private function responseRequest($resposne, $success = false, $responeCode = 300)
    {
        $xml = new SimpleXMLElement('<SOAP-ENV:Envelope/>', LIBXML_NOERROR);
        $xml->addAttribute('xmlns:xmlns:SOAP-ENV', self::SOAPENV);

        /* BODY */
        $Body = $xml->addChild('xmlns:SOAP-ENV:Body');

        if ($success) {
            $this->xmlAppend($Body, $resposne);
        } else {
            $Fault = $Body->addChild('xmlns:SOAP-ENV:Fault');
            $Fault->addChild('faultcode', $responeCode);
            $Fault->addChild('faultstring', $resposne);
        }

        return $xml->asXML();
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
    /// Refactorizacion ///
    ///////////////////////
    public $LIMATOURS_ID = 'LIMATOURS';    // if supply by the conection
    public $CONECTOR_CODE = 'SITEMINDER';    // System currently unavailable

    /** @var Collection */
    public $filters;
    /** @var Collection */
    public $availableRatsPlansRooms = null;
    /** @var Collection */
    public $availableRatesPlans = null;

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
                'translations' => function ($query) {
                    $query->whereHas('language', function ($query) {
                        $query->where('iso', '=', 'en');
                    });
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
