<?php

namespace App\Http\Travelclick\Traits;

use App\RatesPlansRooms;

use DateTime;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use App\Http\Traits\Translations;
use App\Http\Traits\Channel;
use App\Http\Traits\ChannelLogs;

trait Travelclick
{
    use Translations;
    use ChannelLogs;
    use Channel ;

    public $OTA = "http://www.opentravel.org/OTA/2003/05";
    public $TRAVELCLICK_RES_URI = 'http://otaconnect-cert.travelclick.com/v1/certify/rez/push/limatours';
    //https://pushres.ezyield.com/cgi-bin/EZYieldPushInterface.cgi?requestor=limatours-otac

    public $BookingChannelType = "7";
    public $CompanyName = "Limatours";
    public $CompanyCode = "LMT";

    /**
     * @return SimpleXMLElement
     */
    private function setXMLResponse()
    {
        if (!$this->responseMethodName()) {
            $xml = new SimpleXMLElement('<OTA_ErrorRS/>');
        } else {
            $xml = new SimpleXMLElement('<' . $this->responseMethodName() . '/>');
        }

        $xml->addAttribute('xmlns', self::OTA);
        $xml->addAttribute('xmlns:xmlns:xsi', self::XSI);
        $xml->addAttribute('Version', $this->getVersion());
        $xml->addAttribute('TimeStamp', date('c'));
        $xml->addAttribute('EchoToken', $this->getEchoToken());

        $this->XMLResponse = $xml;

        return $this->XMLResponse;
    }

    private function setAvailableRooms($date = null)
    {
        $this->availableRooms = $this->hotel()->rooms()
            ->whereHas('channels', function ($query) {
                $query->where('channels.code', '=', self::CONECTOR_CODE);
            })
            ->with([
                'channels' => function ($query) {
                    $query->where('channels.code', '=', self::CONECTOR_CODE);
                },
                'translations' => function ($query) {
                    $query->whereHas('language', function ($query) {
                        $query->where('iso', '=', 'en');
                    });
                },
                'rates_plan_room' => function ($query) use ($date) {
                    $query->whereHas('channel', function ($query) {
                        $query->where('code', '=', self::CONECTOR_CODE);
                    });
                    $query->with([
                        'rate_plan' => function ($query) {
                            $query->with([
                                'translations' => function ($query) {
                                    $query->whereHas('language', function ($query) {
                                        $query->where('iso', '=', 'en');
                                    });
                                },
                            ]);
                        }
                    ]);
                },
            ])
            ->get();
    }

    /**
     * @param RatesPlansRooms $ratePlanRoom
     * @param null $dates
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRatePlanRoomRates(RatesPlansRooms $ratePlanRoom, $dates = null)
    {
        $select = $ratePlanRoom->calendarys();
        if (!empty($date)) {
            if (is_array($date)) {
                if (count($date) == 2 and isset($date['Start']) and isset($date['End'])) {
                    $select->whereBetween('date', [$date['Start'], $date['End']]);
                } else {
                    $select->whereIn('date', $date);
                }
            } else {
                $select->whereIn('date', '=', $date);
            }
        }
        $select->with([
            'rate' => function ($query) {
            }
        ]);

        return $select->get();
    }

    /**
     * @param RatesPlansRooms $ratePlanRoom
     * @param null $dates
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRatePlanRoomInventories(RatesPlansRooms $ratePlanRoom, $dates = null)
    {
        $select = $ratePlanRoom->inventories();
        if ($dates) {
            if (is_array($dates)) {
                if (count($dates) == 2 and isset($dates['Start']) and isset($dates['End'])) {
                    $select->whereBetween('date', [$dates['Start'], $dates['End']]);
                } else {
                    $select->whereIn('date', $dates);
                }
            } else {
                $select->whereIn('date', '=', $dates);
            }
        }

        return $select->get();
    }

    /**
     * @return Collection
     */
    private function getAvailableRooms()
    {
        return $this->availableRooms;
    }

    private function setAvailableRatesPlans()
    {
        $this->availableRatesPlans = $this->hotel()->rates_plans()
            ->whereHas('rooms.channels', function ($query) {
                $query->where('channels.code', '=', self::CONECTOR_CODE);
                $query->whereNotNull('channel_room.code');
            })
            ->with([
                'translations' => function ($query) {
                    $query->whereHas('language', function ($query) {
                        $query->where('iso', '=', 'en');
                    });
                },
                'rooms' => function ($query) {
                    $query->whereHas('channels', function ($query) {
                        $query->where('channels.code', '=', self::CONECTOR_CODE);
                        $query->whereNotNull('channel_room.code');
                    });
                    $query->wherePivot('channel_id', $this->channelId());
                    $query->with([
                        'translations' => function ($query) {
                            $query->whereHas('language', function ($query) {
                                $query->where('iso', '=', 'en');
                            });
                        },
                        'channels' => function ($query) {
                            $query->where('channels.code', '=', self::CONECTOR_CODE);
                            $query->whereNotNull('channel_room.code');
                        },
                    ]);
                },
            ])
            ->get();
    }

    /**
     * @return Collection
     */
    private function getAvailableRatesPlans()
    {
        return $this->availableRatesPlans;
    }

    public function request(array $headers = [], $content = null)
    {
        $request = new \Illuminate\Http\Request([], [], [], [], [], $_SERVER, $content);
        $request->headers->replace($headers);
        // TODO arreglar que la variable $_SERVER no funciona
        // como somos quien hace el request somo el REMOTE_ADDR de la ip
        if (!isset($_SERVER["REMOTE_ADDR"]))
        {
            $address = "127.0.0.1";
        }else{
            $address = $_SERVER["REMOTE_ADDR"];
        }
        $request->server->add(['REMOTE_ADDR',$address ]);

        return $request;
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

    /**
     * Create, modify and cancel of reservations.
     *
     * @param $hotelRes
     * @param bool $isCertificationTest
     * @return array
     * @throws Exception
     */
// @codingStandardsIgnoreLine
    public function OTA_HotelResNotifRQ(array $hotelRes)
    {
        $this->setChannel('TRAVELCLICK');

        $this->setDateRange($hotelRes['check_in'], $hotelRes['check_out']);

        $xml = new SimpleXMLElement('<OTA_HotelResNotifRQ/>');
        $xml->addAttribute('xmlns', $this->OTA);
        $xml->addAttribute('Version', '1.0');
        $xml->addAttribute('TimeStamp', date('c'));
        $this->createEchoToken();
        $xml->addAttribute('EchoToken', $this->getEchoToken());

        $BookingChannel = $xml->addChild('POS')->addChild('Source')->addChild('BookingChannel');
        $BookingChannel->addAttribute('Type', $this->BookingChannelType);

        $CompanyName = $BookingChannel->addChild('CompanyName',$this->CompanyName);
        $CompanyName->addAttribute('Code', $this->CompanyCode);

        $HotelReservations = $xml->addChild('HotelReservations');
        $HotelReservation = $HotelReservations->addChild('HotelReservation');
        $HotelReservation->addAttribute('ResStatus', 'Commit'); // Reservation status. Must be one of the following: Commit, Cancel, Modify
        $HotelReservation->addAttribute('CreateDateTime', date("Y-m-d\TH:i:s")); // Opcional

        $ResGlobalInfo = $HotelReservation->addChild('ResGlobalInfo');
        $HotelReservationIDs = $ResGlobalInfo->addChild('HotelReservationIDs');
        $HotelReservationID = $HotelReservationIDs->addChild('HotelReservationID');
        $HotelReservationID->addAttribute('ResID_Type', '13'); // Reservation ID type. Currently only "13" is supported
        /**
         * Deberia ser un identificador unico para las reservas de ReservaHotel
         * Nota 2018-05-23: se estara usando el identificador de busqueda
         */
        $HotelReservationID->addAttribute('ResID_Value', $hotelRes['reservation_code']); //

        $ResGuests = $HotelReservation->addChild('ResGuests'); // opcional
        $RoomStays = $HotelReservation->addChild('RoomStays');
        $RPH = 1;
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

            for ($i = 1; $i < (($Room['adult_num']) - 1); $i++) {
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

            $ResGuest = $ResGuests->addChild('ResGuest');
            $ResGuest->addChild('ResGuestRPH', $RPH); // Index by which this guest can be referenced within the RoomStay elements

            // Pasajero Principal
            $Profiles = $ResGuest->addChild('Profiles');
            $ProfileInfo = $Profiles->addChild('ProfileInfo');
            $Profile = $ProfileInfo->addChild('Profile');
            $Customer = $Profile->addChild('Customer');

            $PersonName = $Customer->addChild('PersonName');
            $PersonName->addChild('GivenName', $RoomsGestsType[10][0]['GivenName']);
            $PersonName->addChild('Surname', $RoomsGestsType[10][0]['Surname']);

            $Comments = $ResGuest->addChild('Comments'); // opcional
            $Comment = $Comments->addChild('Comment');
            $Comment->addChild('Text', 'No comment');


            $RoomStay = $RoomStays->addChild('RoomStay');
            /**
             * Index number that can be used as a reference to this RoomStay for partial modification or cancellation.
             * Once this number is established for the given RoomStay by the inital Commit, it must remain consistent
             * even as modifications or cancellations are applied to the reservation (in other words, the RoomStay
             * nodes must not be reindexed as changes are made to the reservation).
             */
            $RoomStay->addAttribute('IndexNumber', $RPH);

            $BasicPropertyInfo = $RoomStay->addChild('BasicPropertyInfo');
            $BasicPropertyInfo->addAttribute('HotelCode', $hotelRes['hotel_channel_code']);

            $TimeSpan = $RoomStay->addChild('TimeSpan');
            $TimeSpan->addAttribute('Start', $hotelRes['check_in']); // Check in date of this stay
            $TimeSpan->addAttribute('End', $hotelRes['check_out']); // Check out date of this stay

            $GuestCounts = $RoomStay->addChild('GuestCounts');
            foreach ($RoomsGestsType as $AgeQualifyingCode => $Guests) {
                $GuestCount = $GuestCounts->addChild('GuestCount');
                $GuestCount->addAttribute('Count', count($Guests));
                $GuestCount->addAttribute('AgeQualifyingCode', $AgeQualifyingCode);
            }

            $Total = $RoomStay->addChild('Total');
            $Total->addAttribute('CurrencyCode', 'USD');
            $Total->addAttribute('AmountAfterTax', priceRound($Room['total_amount_base'])); // Total price AFTER tax. Either this or @AmountBeforeTax are needed, but it is not necessary to include both.
            $Comments = $RoomStay->addChild('Comments'); // opcional
            $Comment = $Comments->addChild('Comment');
            $Comment->addChild('Text', $Room['guest_note']);

            $RoomRates = $RoomStay->addChild('RoomRates');
            $RoomRate = $RoomRates->addChild('RoomRate');

            $RoomRate->addAttribute('NumberOfUnits', '1'); // Number of rooms booked with these stay details. Currently only a value of '1' is supported. Additional rooms must be specified by individual RoomStay elements instead.
            $RoomRate->addAttribute('RoomTypeCode', $Room['room_code']); // Room type identifier. This is expected to match across all RoomRate elements within the given RoomStay
            $RoomRate->addAttribute('RatePlanCode', $Room['rate_plan_code']); // Rate plan identifier

            $Rates = $RoomRate->addChild('Rates');
            foreach ($Room['rates'] as $dateInfo) {
                $Rate = $Rates->addChild('Rate');
                $Rate->addAttribute('RoomPricingType', 'Per night'); // Can be one of the following five values: Per night, Per person, Per person per night, Per stay, Per use
                $Rate->addAttribute('EffectiveDate', $dateInfo['date']); // Effective (start) date of pricing date range (inclusive)
                $Rate->addAttribute('ExpireDate', (new DateTime($dateInfo['date']))->modify('+1 day')->format('Y-m-d')); // Expire (end) date of pricing date range (exclusive)

                $Base = $Rate->addChild('Base');
                $Base->addAttribute('AmountAfterTax', priceRound($dateInfo['total_amount_base'])); // Base price after tax. Either this or @AmountBeforeTax are needed, but it is not necessary to include both.
            }

            $RoomStay->addChild('ResGuestRPHs', $RPH); // Guest RPH associated with this RoomStay
            $RPH++;
        }
//        dd($xml->asXML());
//        header("Content-Type:text/xml");
//        die($xml->asXML());

//        if (App::environment('production') === true) {
            $this->TRAVELCLICK_RES_URI = 'https://pushres.ezyield.com/cgi-bin/EZYieldPushInterface.cgi?requestor=limatours-otac';

//        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->TRAVELCLICK_RES_URI,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array('Content-Type: text/xml'),
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $xml->asXML(),
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

        $request = $this->request($this->getHeaders($requestHeaders), $xml->asXML());
        $response = new \Illuminate\Http\Response($bodyResponse, $status, $this->getHeaders($responseHeaders));

        $error = '';
        $channel_reservation_code = '';
        try {
            if ($status !== 200) {
                throw new \Exception($bodyResponse);
            }

            $objXml = simplexml_load_string($response->getContent());

            if (!$objXml) {
                $error = $response->getContent();
            } else {
                $errorArray = [];
                if ($objXml->Errors->Error) {
                    foreach ($objXml->Errors->Error as $value) {
                        $errorArray[] = (string)$value;
                    }
                }
                if ($objXml->Warnings->Warning) {
                    foreach ($objXml->Warnings->Warning as $value) {
                        $errorArray[] = (string)$value;
                    }
                }
                if (count($errorArray) > 0) {
                    $error = implode(' | ', $errorArray);
                }

                if ($error != '') {
                    throw new \Exception($error);
                } else {
                    $channel_reservation_code = $objXml->HotelReservations->HotelReservation->ResGlobalInfo->HotelReservationIDs->HotelReservationID->attributes()->ResID_Value->__toString();
                }
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        $success = (bool)!$error;

        $this->putXmlLog($request, $response, $this->getChannel(), 'OTA_HotelResNotifRQ', $hotelRes['reservation_code'], $success);

        return [
            'success' => $success ? true : false,
            'error' => $error,
            'channel_reservation_code' => $channel_reservation_code
        ];
    }

// @codingStandardsIgnoreLine
    public function OTA_CancelRQ($hotelRes)
    {
        $this->setChannel('TRAVELCLICK');

        $xml = new SimpleXMLElement('<OTA_HotelResNotifRQ/>');
        $xml->addAttribute('xmlns', $this->OTA);
        $xml->addAttribute('Version', '1.0');
        $xml->addAttribute('TimeStamp', date('c'));
        $this->createEchoToken();
        $xml->addAttribute('EchoToken', $this->getEchoToken());

        $BookingChannel = $xml->addChild('POS')->addChild('Source')->addChild('BookingChannel');
        $BookingChannel->addAttribute('Type', $this->BookingChannelType);

        $CompanyName = $BookingChannel->addChild('CompanyName',$this->CompanyName);
        $CompanyName->addAttribute('Code', $this->CompanyCode);

        $HotelReservations = $xml->addChild('HotelReservations');
        $HotelReservation = $HotelReservations->addChild('HotelReservation');
        $HotelReservation->addAttribute('ResStatus', 'Cancel'); // Reservation status. Must be one of the following: Commit, Cancel, Modify
//        $HotelReservation->addAttribute('LastModifyDateTime', date("Y-m-d\TH:i:s",strtotime($hotelRes['last_modify_date_time']))); // Opcional

        $ResGlobalInfo = $HotelReservation->addChild('ResGlobalInfo');
        $HotelReservationIDs = $ResGlobalInfo->addChild('HotelReservationIDs');
        $HotelReservationID = $HotelReservationIDs->addChild('HotelReservationID');
        $HotelReservationID->addAttribute('ResID_Type', '13'); // Reservation ID type. Currently only "13" is supported
        /**
         * Deberia ser un identificador unico para las reservas de ReservaHotel
         * Nota 2018-05-23: se estara usando el identificador de busqueda
         */
        $HotelReservationID->addAttribute('ResID_Value', $hotelRes['reservation_code']); //

//        dd($xml->asXML());
//        header("Content-Type:text/xml");
//        die($xml->asXML());

//        if (App::environment('production') === true) {
//            $this->TRAVELCLICK_RES_URI = 'https://pushres.ezyield.com/cgi-bin/EZYieldPushInterface.cgi?requestor=aurorasys-otac';
            $this->TRAVELCLICK_RES_URI = 'https://pushres.ezyield.com/cgi-bin/EZYieldPushInterface.cgi?requestor=limatours-otac';
//        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->TRAVELCLICK_RES_URI,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array('Content-Type: text/xml'),
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $xml->asXML(),
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

        $request = $this->request($this->getHeaders($requestHeaders), $xml->asXML());
        $response = new \Illuminate\Http\Response($bodyResponse, $status, $this->getHeaders($responseHeaders));

        $error = '';
        $channel_reservation_code = '';
        try {
            if ($status !== 200) {
                throw new \Exception($bodyResponse);
            }

            $objXml = simplexml_load_string($response->getContent());

            if (!$objXml) {
                $error = $response->getContent();
            } else {
                $errorArray = [];
                if ($objXml->Errors->Error) {
                    foreach ($objXml->Errors->Error as $value) {
                        $errorArray[] = (string)$value;
                    }
                }
                if ($objXml->Warnings->Warning) {
                    foreach ($objXml->Warnings->Warning as $value) {
                        $errorArray[] = (string)$value;
                    }
                }
                if (count($errorArray) > 0) {
                    $error = implode(' | ', $errorArray);
                }

                if ($error != '') {
                    throw new \Exception($error);
                } else {
                    $channel_reservation_code = $objXml->HotelReservations->HotelReservation->ResGlobalInfo->HotelReservationIDs->HotelReservationID->attributes()->ResID_Value->__toString();
                }
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        $success = (bool)!$error;

        $this->putXmlLog($request, $response, $this->getChannel(), 'OTA_HotelResCancelNotifRQ', $hotelRes['reservation_code'], $success);

        return [
            'success' => $success ? true : false,
            'error' => $error,
            'channel_reservation_code' => $channel_reservation_code
        ];
    }
}
